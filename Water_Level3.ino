#include <SoftwareSerial.h>
#include <LiquidCrystal_I2C.h>
#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

#define MAX_TRYING 3

uint8_t mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
uint8_t arduinoIP[] = { 192, 168, x, x };
uint8_t gatewayIP[] = { 192, 168, x, x };
uint8_t subnetIP[] = { 255, 255, x, x };
uint8_t dnsIP[] = { x, x, x, x };
IPAddress server_addr(192, 168, x, x);

char user[] = "root";
char password[] = "";

EthernetClient client;
MySQL_Connection conn((Client *)&client);

SoftwareSerial mySerial(6, 5);
unsigned char data[4] = {};
float distance;

unsigned long pumpStartTime = 0;
unsigned long pumpOnTime = 0;
bool malfHandled = false;

void setup()
{
  Serial.begin(57600);
  mySerial.begin(9600);
  pinMode(7, OUTPUT);

  Ethernet.begin(mac_addr, arduinoIP, dnsIP, gatewayIP, subnetIP);
  delay(1000);

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
  do
  {
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
          if (pumpStartTime != 0)
          { 
            pumpStartTime = 0;
            recordPumpEvent("OFF");
          }
          digitalWrite(7, HIGH);
          Serial.println("Air Penuh");
          delay(2000);
        }
        else if (jarak >= 65 && jarak <= 80)
        {
          if (pumpStartTime == 0)
          {
            pumpStartTime = millis();
            recordPumpEvent("ON");
          }

          digitalWrite(7, LOW);
          Serial.println("Isi");
        }
        else if (jarak > 80 && !malfHandled)
        {
          Serial.println("Pompa Malfunc");
          recordPumpEvent("MALF");
          malfHandled = true;
        }
        
        if (jarak <= 80)
        {
          malfHandled = false;
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

void recordPumpEvent(const char *status)
{
   char query[128];
  MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);

  if (strcmp(status, "OFF") == 0)
  {
    sprintf(query, "INSERT INTO db_sensor.t_pompa (pos, area, status, waktu) VALUES (1, 'Area1', 'OFF', NOW())");
  }
  else if (strcmp(status, "ON") == 0)
  {
    sprintf(query, "INSERT INTO db_sensor.t_pompa (pos, area, status, waktu) VALUES (1, 'Area1', 'ON', NOW())");
  }
  if (strcmp(status, "MALF") == 0)
  {
    sprintf(query, "INSERT INTO db_sensor.t_pompa (pos, area, status, waktu) VALUES (1, 'Area1', 'MALF', NOW())");
  }

  cur_mem->execute(query);
  delete cur_mem; 
}


