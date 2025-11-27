@echo off
setlocal

echo Vérification de Docker Desktop...

REM Vérifier si Docker Desktop est lancé
tasklist /FI "IMAGENAME eq Docker Desktop.exe" | find /I "Docker Desktop.exe" >NUL
if %ERRORLEVEL% NEQ 0 (
    echo Docker n'est pas lancé. Démarrage...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    echo Attente du démarrage de Docker... (30 à 90 secondes)
)

:waitDocker
docker info >NUL 2>&1
if %ERRORLEVEL% NEQ 0 (
    timeout /T 3 >NUL
    goto waitDocker
)

echo Docker est prêt.

echo Lancement de l'application...
cd /D "%~dp0"
docker compose up -d

echo Ouverture de l'application dans le navigateur...
start http://localhost:8081

echo Terminé.
exit
