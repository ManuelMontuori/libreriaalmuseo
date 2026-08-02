@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul

set URL=https://www.almuseo.it/mail.php

echo ===============================================
echo  TEST 1 - Nessun captcha, nessun honeypot
echo  (bot semplice che POSTa direttamente)
echo  Atteso: 403 - Captcha mancante.
echo ===============================================
curl --ssl-no-revoke -s -o response1.txt -w "HTTP Status: %%{http_code}\n" -X POST %URL% ^
  -d "form_type=contatti" ^
  -d "nome=Test" ^
  -d "email=test@test.com" ^
  -d "messaggio=Prova bot 1"
type response1.txt
echo.
echo -----------------------------------------------
echo.

echo ===============================================
echo  TEST 2 - Honeypot compilato
echo  (bot che riempie anche il campo nascosto)
echo  Atteso: 403 (bloccato subito, prima di tutto)
echo ===============================================
curl --ssl-no-revoke -s -o response2.txt -w "HTTP Status: %%{http_code}\n" -X POST %URL% ^
  -d "form_type=contatti" ^
  -d "website=spam" ^
  -d "nome=Test" ^
  -d "email=test@test.com" ^
  -d "messaggio=Prova bot 2"
type response2.txt
echo.
echo -----------------------------------------------
echo.

echo ===============================================
echo  TEST 3 - Token Turnstile finto/non valido
echo  (bot un po' piu' furbo, manda un token a caso)
echo  Atteso: 403 - Captcha non valido.
echo ===============================================
curl --ssl-no-revoke -s -o response3.txt -w "HTTP Status: %%{http_code}\n" -X POST %URL% ^
  -d "form_type=contatti" ^
  -d "cf-turnstile-response=token_finto_123456" ^
  -d "nome=Test" ^
  -d "email=test@test.com" ^
  -d "messaggio=Prova bot 3"
type response3.txt
echo.
echo -----------------------------------------------
echo.

echo ===============================================
echo  TEST 4 - Metodo GET invece di POST
echo  Atteso: 405 (metodo non consentito)
echo ===============================================
curl --ssl-no-revoke -s -o response4.txt -w "HTTP Status: %%{http_code}\n" %URL%
type response4.txt
echo.
echo -----------------------------------------------
echo.

echo Tutti i test completati.
echo I file response1.txt - response4.txt contengono le risposte complete.
del response1.txt response2.txt response3.txt response4.txt >nul 2>nul

pause