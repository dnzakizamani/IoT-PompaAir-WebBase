#include <SoftwareSerial.h>
#include <LiquidCrystal_I2C.h>
#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

#define MAX_TRYING 3

// byte mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xEE };
// byte arduinoIP[] = { 192, 168, 28, 190 };    
// byte gatewayIP[] = { 192, 168, 29, 254 };
// byte subnetIP[] = { 255, 255, 254, 0 };
// byte dnsIP[] = { 8, 8, 8, 8 };
uint8_t mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xEE };
uint8_t arduinoIP[] = { 192, 168, 28, 190 };    
uint8_t gatewayIP[] = { 192, 168, 29, 254 };
uint8_t subnetIP[] = { 255, 255, 254, 0 };
uint8_t dnsIP[] = { 8, 8, 8, 8 };
IPAddress server_addr(192, 168, 1, 13);

char user[] = "root";           
char password[] = "Bc8574";

EthernetClient client;
MySQL_Connection conn((Client *)&client);



LiquidCrystal_I2C lcd(0x27, 16, 2); // Kalau gagal ganti 0x3F ke 0x27 for
SoftwareSerial mySerial(11, 10); // RX, TX
unsigned char data[4] = {};
float distance;

void setup()
{
  Serial.begin(57600);
  mySerial.begin(9600);
  pinMode(7, OUTPUT);

  lcd.init();                      // initialize the lcd
  lcd.backlight();

  // Add the following lines to establish the Ethernet connection
  Ethernet.begin(mac_addr, arduinoIP, dnsIP, gatewayIP, subnetIP);
  delay(1000);

  // Establish a connection to the MySQL server
  if (conn.connect(server_addr, 3306, user, password))
  {
    Serial.println("Connected to MySQL server!");
  }
  else
  {
    Serial.println("Connection failed.");
  }

 

}

void loop()
{
  
  ultra();
}

void ultra() {
  do {
    for (int i = 0; i < 4; i++)
    {
      data[i] = mySerial.read();
    }
  } while (mySerial.read() == 0xff);

  mySerial.flush();

  if (data[0] == 0xff)
  {
    int sum;
    sum = (data[0] + data[1] + data[2]) & 0x00FF;
    if (sum == data[3])
    {
      distance = (data[1] << 8) + data[2];
      if (distance > 30)
      {

        int jarak = distance / 10;

        if (jarak <= 15)
        {
          lcd.clear();
          digitalWrite(7, HIGH);
          lcd.setCursor(0, 0);
          lcd.print("Air Penuh");
          Serial.println("Air Penuh");
          lcd.setCursor(0, 1);
          lcd.print("Terima Kasih");
          delay(2000);
          lcd.clear();
        }
        else if (jarak >= 65 && jarak <=80)
        {
          lcd.clear();
          digitalWrite(7, LOW);
          lcd.setCursor(0, 1);
          lcd.print("Pompa Menyala");
          Serial.println("Isi");
        }
        else if (jarak >= 80)
        {
          lcd.clear();
          lcd.setCursor(0, 1);
          lcd.print("Pompa Malfunc");
          Serial.println("Pompa Malfunc");
        }

        delay(500);
      }
      else
      {
        // Serial.println("Below the lower limit");
      }
    }
    else
    {
      // Serial.println("ERROR");
    }
  }
  delay(100);
}
