/*
  IoT project for a smart gated community managed by an ESP32 module.
  This project is for an IoT lab class final exam.
  Università degli studi della Campania Luigi Vanvitelli dipartimento di ingegneria.
*/

#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>


//definizione pin GPIO dei sensori
  //temperatura
#define DHTPIN 4
  //Sensore IR
#define EchoPin 12
#define TriggerPin 14
  //servo
#define ServoPin 15
  //fotoresistore
#define AnalogPin 2

//creazione oggetto della classe servo
Servo myservo;

#define MAX_DISTANCE 7500 //distanza massima del sensore IR

//definizione tipi di sensore (non per ogni sensore)
#define DHTTYPE DHT22

float timeout=MAX_DISTANCE*60; //tempo di timeout per il sensore IR
int soundspeed=340;

//soluzione per far funzionare i sensori insieme
//in pratica usiamo un timer che varia in runtime e lo confrontiamo con il tempo desiderato
//controlleremo il tempo passato e nel caso sia passato il tempo necessario allora continueremo con la rilevazione
unsigned long lastTimeTemp=0;
unsigned long lastTimeAfar=0;

//definisce un oggetto DHT (sensore di temperatura)
DHT dht(DHTPIN, DHTTYPE);

//ssid e password del wifi (da cambiare ogni volta)
const char* ssid="FASTWEB-WV1GBU";
const char* password="62RGOCYWHT";

//api token e user token per utilizzare il servizio pushover
const char* apiToken = "aqy6bkr4c7aqucpec1743nsj38g2ce";
const char* userToken = "uwemowyqrd7ogguo8csr2fg4fo6hxu";

//certificato SSL
const char *PUSHOVER_ROOT_CA = "";

//setting del web server sulla porta 80
WiFiServer server(80);

//URL o IP con path (da cambiare ogni volta)
const char* serverNameSens="http://192.168.1.126/PHP-examples/Temp.php";
const char* ApiEndpoint="https://api.pushover.net/1/messages.json";
const char* serverNoty="http://192.168.1.126/PHP-examples/Noti.php";

//stringa per la richiesta HTTP
String header;

//lampioni
#define LED1 25
#define LED2 26
#define LED3 27

void setup() {

  //setting dei led in modalità output
  pinMode(LED1, OUTPUT);
  pinMode(LED2, OUTPUT);
  pinMode(LED3, OUTPUT);

  //setta il baud rate per la connessione seriale
  Serial.begin(115200);

  //setting della connessione wifi
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while(WiFi.status() != WL_CONNECTED){
    delay(1000);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("connesso al wifi con ip:");
  Serial.println(WiFi.localIP());
  server.begin(); //faccio partire il server web

  //setting del sensore IR
  pinMode(TriggerPin,OUTPUT);
  pinMode(EchoPin,INPUT);

  //setting servo
  myservo.setPeriodHertz(50);
  myservo.attach(ServoPin, 500, 2500);

  //faccio partire il sensore di temperatura
  dht.begin();
}

void loop() {
  //accensione e spegnimento lampioni in base alla luce ambientale
  int luce=analogRead(AnalogPin);
  if(luce>3300){ //se incomincia a far buio
    digitalWrite(LED1, HIGH);
    digitalWrite(LED2, HIGH);   //accende tutte le 3 luci
    digitalWrite(LED3, HIGH);
  }else{ //altrimenti
    digitalWrite(LED1, LOW);
    digitalWrite(LED2, LOW);  //le spegne
    digitalWrite(LED3, LOW);
  }

  //distanza ogni 10 secondi
  if((millis()-lastTimeAfar)>10000){
    //SerialDistTest(); //printo su seriale per testare i valori
    float distance = getSonar();
    if(distance>25.00){ //avendo un range di più di 25 cm e se considerassimo 25 cm come distanza massima ci ritroveremo con diversi problemi forziamo ad avere una distanza massima di 25 cm
      distance=25.00;
    }
    Serial.println(String(distance) +" cm");
    float fillPercent=1-(distance/25);  //percentuale di riempimento (da aggiustare)
    Serial.println(String(fillPercent*100)+"%");  //stampa la percentuale di riempimento
    sendToServer(serverNameSens, "fill="+String(fillPercent)+"&id=001");  //invia i dati al db
    if(fillPercent>0.75){ //se pieno più del 75% manda una notifica al netturbino
      notify("il cassonetto con id 001 è pieno al "+String(fillPercent*100)+"% si prega di andare a svuotarlo", "cestino 001 pieno", ""); //si riporta alla funzione notify che sfrutta una post verso l'api di pushover
    }
    lastTimeAfar=millis();
  }

  //manda la temperatura ogni 60 secondi
  if((millis()-lastTimeTemp)>60000){
    //setto due variabili per la lettura di umidità e temperatura (in gradi centigradi)
    float h=dht.readHumidity();
    float t=dht.readTemperature();

    //stampo a seriale i valori per scopo di test
    SerialTempTest(t, h);

    //invio con HTTP POST i dati ricevuti per essere salvati su DB e per poi essere riutilizzati
    if(!sendToServer(serverNameSens, "key=temperatura&temp="+String(t)+"&hum="+String(h))){
      Serial.print("insuccesso temperatura");
    } 
    if(t>35.00){
      notify("","caldo eccessivo","Residente");
    }else if(t<5.00){
      notify("","freddo eccessivo","Residente");
    }
    lastTimeTemp=millis();
  }

  //funzionamento del servo: grazie ad una richiesta http inviata dall'esterno
  //il portone si apre per poi richiudersi dopo un tot di tempo
  WiFiClient client=server.available(); //capisce se c'è un client che intende connettersi
  if(client){ //se c'è un client 
    Serial.println("client connected");
    while(client.connected()){  //finchè il client è connesso
      if(client.available()){   //se c'è un una richiesta dal client
        header=client.readStringUntil('\n');  //legge il messaggio fino all'andata a capo
        if(header.indexOf("GET /15/open")>=0){  //se la richiesta è tramite metodo GET e sopratutto se richiede l'apertura
          myservo.write(90);    //apre la sbarra di 90°
          delay(10000);         //aspetta 10 secondi (10000 ms)
          myservo.write(0);     //richiude la sbarra
        }
        while(client.read()>0); //finisce di leggere la richiesta
      }
    } 
    client.stop();              //stoppa la connessione del client
    Serial.println("Client disconnesso");
  }

}

//Funzione che ritorna la distanza registrata dal sensore
float getSonar(){
  unsigned long pingTime;
  float distance;
  // faccio partire il triggerpin per 10 microsecondi per poi farlo spegnere
  digitalWrite(TriggerPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(TriggerPin, LOW);
  // Wait HC-SR04 returning to the high level and measure out this waiting time 
  pingTime = pulseIn(EchoPin, HIGH, timeout);
  // calcoliamo la distanza rispetto al pingtime
  distance = (float)pingTime * soundspeed / 2 / 10000;
  return distance; // ritorno la distanza
}

//funzione per stampare su seriale la distanza per testare il sensore IR
void SerialDistTest(){
  Serial.print("Distanza ");
  Serial.print(getSonar());
  Serial.println(" cm");
}
//funzione per stampare su seriale la temperatura per testare il funzionamento del sensore
void SerialTempTest(float tmp, float um){

  //controllo se la lettura è avvenuta o meno
  if(isnan(um) || isnan(tmp)){
    Serial.println(F("impossibile leggere la temperatura"));
  }

  //print su seriale per controllo dati
  Serial.print(F("C° :"));
  Serial.print(tmp);
  Serial.print(F(" Umidità: "));
  Serial.print(um);
  Serial.println(F(" %"));
}

bool sendToServer(String serverName, String message){
      if(WiFi.status()== WL_CONNECTED){
      WiFiClient client;
      HTTPClient http;

      //inizio connessione http
      http.begin(client, serverName);

      /*
        header che indica il tipo di contenuto del body (ricordiamo che POST invia i dati nel body e non nell'header)
        in questo caso inviamo i dati sottoforma di url, potremo farlo anche tramite json o plain text
      */
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      //creazione e invio dei dati del sensore di temperatura tramite metodo HTTP POST
      String httpRequestData=message;
      int httpResponse= http.POST(httpRequestData);

      //print su seriale del codice di risposta HTTP per controllo
      Serial.print("HTTP RC: ");
      Serial.println(httpResponse);

      //chiusura connessione HTTP
      http.end();
      return true; //POST riuscita
    }
    else{
      return false;//POST fallita
    }
}

bool notify(String msg, String title, String device){
  if (WiFi.status() == WL_CONNECTED) {
    // Create a JSON object with notification details
    // Check the API parameters: https://pushover.net/api
    StaticJsonDocument<512> notification; 
    notification["token"] = apiToken; //required
    notification["user"] = userToken; //required
    notification["message"] = msg; //required
    notification["title"] = title; //optional
    notification["device"] = device;
    notification["url"] = ""; //optional
    notification["url_title"] = ""; //optional
    notification["html"] = ""; //optional
    notification["priority"] = ""; //optional
    notification["sound"] = "cosmic"; //optional
    notification["timestamp"] = ""; //optional

    // Serialize the JSON object to a string
    String jsonStringNotification;
    serializeJson(notification, jsonStringNotification);

    // Create a WiFiClientSecure object
    //WiFiClientSecure Sclient;
    // Set the certificate
    //Sclient.setCACert(PUSHOVER_ROOT_CA);

    // Create an HTTPClient object
    HTTPClient http;

    Serial.println("cacas");
    // Specify the target URL
    http.begin(ApiEndpoint);
    Serial.println("cucus");

    // Add headers
    http.addHeader("Content-Type", "application/json");

    // Send the POST request with the JSON data
    int httpResponseCode = http.POST(jsonStringNotification);

    // Check the response
    if (httpResponseCode > 0) {
      Serial.printf("HTTP response code: %d\n", httpResponseCode);
      String response = http.getString();
      Serial.println("Response:");
      Serial.println(response);
    } else {
      Serial.printf("HTTP response code: %d\n", httpResponseCode);
    }
    
    // Close the connection
    http.end();
    return true;
  }
  return false;
}


    // P.F. 25/05/2024 02:27
