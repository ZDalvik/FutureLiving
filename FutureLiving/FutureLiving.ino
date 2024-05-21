/*
  IoT project for a smart gated community managed by an ESP32 module.
  This project is for an IoT lab class final exam.
  Università degli studi della Campania Luigi Vanvitelli dipartimento di ingegneria.
*/
#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>

//definizione pin GPIO dei sensori
  //temperatura
#define DHTPIN 4
  //Sensore IR
#define EchoPin 12
#define TriggerPin 14
  //servo
#define ServoPin 15

//creazione oggetto della classe servo
Servo myservo;

#define MAX_DISTANCE 700 //distanza massima del sensore IR

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

//setting del web server sulla porta 80
WiFiServer server(80);

//URL o IP con path (da cambiare ogni volta)
const char* serverName="http://192.168.1.126/PHP-examples/Temp.php";

//stringa per la richiesta HTTP
String header;

void setup() {
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
  server.begin();

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

  //distanza ogni 10 secondi
  if((millis()-lastTimeAfar)>10000){
    SerialDistTest();
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
    if(!sendToServer("temp="+String(t)+"&hum="+String(h))){
      Serial.print("insuccesso temperatura");
    } 
    lastTimeTemp=millis();
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

bool sendToServer(String message){
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

    // P.F. 19/05/2024 19:53
