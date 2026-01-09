PROJECT MANAGER:
- Supardianto, S.ST., M.Eng.

TEAM:
1. Terra Faqih Satria Madjid (Leader) - Fullstack Developer
2. Alya Ghaitsa Salsabila - Fullstack Developer
3. Rivo Nyawan Situmorang - Frontend Developer
4. Bella Fadhilla Khairunnisyah Effendi - Business Analyst
5. Rizky Eko Pratama - UI/UX Designer
6. Hasanun Nisa - UI/UX Designer

Note:
Please install XAMPP and import the following SQL from the SQL folder to use the application.

Log to Admin:
NIK: 2171032502070007
Password: 2171032502070007

Installation

🔐 How to Enable SSL (HTTPS) on Localhost XAMPP Using Mkcert
This guide explains how to enable HTTPS (SSL) on your localhost using Mkcert so your local website can run in a secure environment similar to production.

📦 Requirements
 - XAMPP (Apache)
 - Mkcert for Windows
 - Administrator access

📁 Installation Steps
1. Prepare Mkcert

 - Download mkcert.exe
 - Rename the file to:
   Mkcert.exe

 - Move the file into:
   C:\Windows\System32

2. Install Mkcert Local Certificate Authority

 - Open Command Prompt as Administrator and run:
   mkcert -install

3. Generate SSL Certificate for Localhost

 - Still in Command Prompt, run:
   mkcert localhost 127.0.0.1 ::1

 - This will generate two files:
   localhost.pem
   localhost-key.pem

4. Move Certificates to XAMPP

 - Move both generated files to:
   XAMPP\apache\conf\ssl\
   If the ssl folder does not exist, create it manually.

5. Enable Apache SSL Module

 - Open XAMPP Control Panel → Config → httpd.conf
   Make sure these lines are NOT commented:
   LoadModule ssl_module modules/mod_ssl.so
   Include conf/extra/httpd-ssl.conf
   Save and close the file.

6. Configure SSL Virtual Host

 - Open:
   XAMPP\apache\conf\extra\httpd-ssl.conf
   Delete all existing contents and replace with:

-----------------------------------------------------------------

  Listen 443
  
  <VirtualHost *:443>
      ServerName localhost
      DocumentRoot "C:/xampp/htdocs"
  
      SSLEngine on
      SSLCertificateFile "C:/xampp/apache/conf/ssl/localhost.pem"
      SSLCertificateKeyFile "C:/xampp/apache/conf/ssl/localhost-key.pem"
  
      <Directory "C:/xampp/htdocs">
          Options Indexes FollowSymLinks
          AllowOverride All
          Require all granted
      </Directory>
  </VirtualHost>

-----------------------------------------------------------------

 - Save and close the file.

7. Restart Apache

 - Restart Apache from the XAMPP Control Panel.

🎉 Done!

 - Now your localhost is accessible securely via:
   https://localhost
