/*
  IoT project for a smart gated community managed by an ESP32 module.
  This project is for an IoT lab class final exam.
  Università degli studi della Campania Luigi Vanvitelli dipartimento di ingegneria.
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>

//definizione pin GPIO del sensore
#define DHTPIN 4

//definizione tipi di sensore (non per ogni sensore)
#define DHTTYPE DHT22

//definisce un oggetto DHT (sensore di temperatura)
DHT dht(DHTPIN, DHTTYPE);

//ssid e password del wifi (da cambiare ogni volta)
const char* ssid="FASTWEB-WV1GBU";
const char* password="62RGOCYWHT";

//URL o IP con path (da cambiare ogni volta)
const char* serverName="http://192.168.1.126/PHP-examples/Temp.php";

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

  //faccio partire il sensore di temperatura
  dht.begin();
}

void loop() {

  //la temperatura viene registrata ogni 60 secondi (1 minuto), BISOGNA TROVARE UN MODO MIGLIORE PER FARLO ALTRIMENTI VERRANNO AGGIORNATI OGNI MINUTO ANCHE GLI ALTRI SENSORI
  //la soluzione potrebbe essere quella di considerare solo la temperatura e umidità dopo un certo lasso di tempo e far continuare a registrare la temperatura in ogni caso,
  //per esempio, il sensore continua a registrare continuamente temperature ogni minuto ma un contatore fa si che si registri solo la temperatura registrata dopo un minuto.
  delay(60000);

  //setto due variabili per la lettura di umidità e temperatura (in gradi centigradi)
  float h=dht.readHumidity();
  float t=dht.readTemperature();

  //controllo se la lettura è avvenuta o meno
  if(isnan(h) || isnan(t)){
    Serial.println(F("impossibile leggere la temperatura"));
  }

  //print su seriale per controllo dati
  Serial.print(F("C° :"));
  Serial.print(t);
  Serial.print(F(" Umidità: "));
  Serial.print(h);
  Serial.println(F(" %"));

  //controlla connessione per instaurazione connessione http 
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
    String httpRequestData="temp="+String(t)+"&hum="+String(h);
    int httpResponse= http.POST(httpRequestData);

    //print su seriale del codice di risposta HTTP per controllo
    Serial.print("HTTP RC: ");
    Serial.println(httpResponse);

    //chiusura connessione HTTP
    http.end();
  }
  else{
    Serial.println("nessuna connessione WiFi");
  }
}

    // P.F. 18/05/2024 20:06
