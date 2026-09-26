@echo off
set "JAVA_HOME=C:\PROGRA~1\Android\ANDROI~1\jbr"
set "PATH=%JAVA_HOME%\bin;%PATH%"
echo Java home: %JAVA_HOME%
java -version
echo.
echo Building APK...
call gradlew.bat assembleDebug
