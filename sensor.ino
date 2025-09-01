#include <Arduino.h>
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>//ESP8266HTTPClient.h ESP8266WiFi.h
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <EEPROM.h>   

const char* ssid     = "IOT";
const char* password = "IOT12345";
String payload;
int count;
void data_to_web();

#include "RoninDMD.h"          // Include lib & font 
#include "Arial_Black_16.h"
#define FONT Arial_Black_16

#define WIDTH 2    //panel count  // Set width & height
#define HEIGHT 1
int speed=50;
RoninDMD P10(WIDTH, HEIGHT);

String Message = "Waiting For NET Connection";

void setup() 
{
  Serial.begin(9600);
  
  P10.begin();              // Begin the display & font
  P10.setFont(FONT);
  P10.setBrightness(20);    // Set the brightness
 // P10.drawText(0 , 0, " :) "); // P10.drawText(position x , position y, String type text);
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) 
  {
    delay(1000);
    Serial.println("Connecting,,,");
    Scrolling_text(0 , speed , "Waiting For WIFI..." ,2);
  }   
  Serial.println("Connected...");
  
 Scrolling_text(0 , speed , "WIFI Connected" ,2);
}

void loop() 
{
  //count++;
  //if (count==2){count=0; data_to_web();}
  data_to_web();
  P10.loop();          // Run DMD loop
  if (Serial.available() > 0) { Message =  Serial.readString(); }  // Save message from serial
  Scrolling_text(0 , speed , Message ,2); // Call the function to write scrolling text on screen.
                                     // like -> Scrolling_text( position y , scroll speed, String type text);
                                     // or for not scroll -> P10.drawText(position x , position y, String type text);
  delay(100);
}

void data_to_web()
{
 if (WiFi.status() == WL_CONNECTED)  //Check WiFi connection status
    {      
     Serial.println() ;
     WiFiClient client;     
     HTTPClient http;  //Declare an object of class HTTPClient
     int httpCode=0;
     http.begin(client,"http://technotalents.co.in/srec/save.php"); //Send the request  
     //https://technotalents.co.in/srec/index.php //Message send page
     
     httpCode = http.GET();                       
     if (httpCode > 0) //Check the returning code
         { 
          Message = http.getString();   //Get the request response payload               
          int lengt = Message.length(); 
          //Serial.print("httpCode:");  Serial.println(httpCode); 
          Serial.print("Message:"); Serial.println(Message); 
          //Serial.print("lengt:");     Serial.println(lengt);
          httpCode=0;    
         }    
      http.end();
      delay(100);

     http.begin(client,"http://technotalents.co.in/srec/speed.php"); //Send the request 
     httpCode = http.GET();   
     if (httpCode > 0) //Check the returning code
         { 
          String speeeed = http.getString();  
          speed = (speeeed[3]-'0')*10 + (speeeed[4]-'0')*1;
          speed = 99-speed;
          int lengt = speeeed.length();
         // Serial.print("httpCode:");    Serial.println(httpCode); 
         // Serial.print("speeeed:");     Serial.println(speeeed); 
         // Serial.print("speeeed[3]:");  Serial.println(speeeed[3]); 
         // Serial.print("speeeed[4]:");  Serial.println(speeeed[4]);
          Serial.print("speed:");       Serial.println(speed); 
         // Serial.print("lengt:");       Serial.println(lengt);              
          httpCode=0; 
         }    
      http.end();  
      delay(100);    
   }
}
void Scrolling_text(int text_height , int scroll_speed , String scroll_text ,int direction) 
{
  static uint32_t pM ;
  pM = millis();
  //static uint32_t x = 0;
  scroll_text = scroll_text + " ";

  bool  scrl_while = 1 ;
  int dsp_width = P10.width();
  int txt_width = P10.textWidth(scroll_text);

  if(direction==1)//Still
  {
   static uint32_t x =64;
   while (scrl_while == 1) 
    {
      P10.loop();
      delay(1);
      if (millis() - pM > scroll_speed) 
       {
        P10.setFont(FONT);
        P10.drawText(dsp_width - x, text_height, scroll_text);
        //x++;
        if (x >  txt_width + dsp_width) 
           {
            x = 64 ;
            scrl_while = 0 ;
           }
        pM = millis();
       }
    }
  }

  if(direction==2)//Right to left
  {
   static uint32_t x = 0;
   while (scrl_while == 1) 
    {
      P10.loop();
      delay(1);
      if (millis() - pM > scroll_speed) 
       {
        P10.setFont(FONT);
        P10.drawText(dsp_width - x, text_height+1, scroll_text);
        x++;
        if (x >  txt_width + dsp_width) 
           {
            x = 0 ;
            scrl_while = 0 ;
           }
        pM = millis();
       }
    }
  }

  if(direction==3)//Left to right  
  {
   static uint32_t x = 64;
   while (scrl_while == 1) 
    {
      P10.loop();
      delay(1);
      if (millis() - pM > scroll_speed) 
       {
        P10.setFont(FONT);
        P10.drawText(dsp_width - x, text_height, scroll_text);
        x--;
        if (x >  txt_width + dsp_width) 
           {
            x = 64 ;
            scrl_while = 0 ;
           }
        pM = millis();
       }
    }
  }

  if(direction==4)//Top to bottom  
  {
   static uint32_t x = 64; text_height=0;
   while (scrl_while == 1) 
    {
      P10.loop();
      delay(1);
      if (millis() - pM > scroll_speed) 
       {
        P10.setFont(FONT);
        P10.drawText(dsp_width - x, text_height+=1, scroll_text);
        if(text_height>13){text_height=0;}
        //x--;
        if (x >  txt_width + dsp_width) 
           {
            x = 64 ;
            scrl_while = 0 ;
           }
        pM = millis();
       }
    }
  }


  if(direction==5)//Bottom  to top
  {
   static uint32_t x = 64; text_height=16;
   while (scrl_while == 1) 
    {
      P10.loop();
      delay(1);
      if (millis() - pM > scroll_speed) 
       {
        P10.setFont(FONT);
        P10.drawText(dsp_width - x, text_height-=1, scroll_text);
        if(text_height<1){text_height=16;}
        //x--;
        if (x >  txt_width + dsp_width) 
           {
            x = 64 ;
            scrl_while = 0 ;
           }
        pM = millis();
       }
    }
  }
}
Displaying d8a37a29-4de7-4d1e-9563-400c6cd64cc5.txt.
