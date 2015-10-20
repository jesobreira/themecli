@echo off
echo PHPTheme-CLI compiling
echo.
IF EXIST "bin\themecli.exe" (
	del bin\themecli.exe
)
cd %0
IF NOT EXIST "bamcompile.exe" GOTO nobamcompile
echo Please wait...
:: bamcompile.exe compile.bcp :: buggy
bamcompile.exe -c -i:icon.ico main.php bin\themecli.exe
echo Compiling complete!
cmd /k

:nobamcompile
echo You need to download bamcompile.exe and put it in this folder first.
echo Open bamcompile website? (y/n)
set /p opensite=
if %opensite%==y start http://www.bambalam.se/bamcompile/