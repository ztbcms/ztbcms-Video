@echo off

call phpunit --bootstrap Service/VideoService.class.php tests/VideoTest

echo.

pause
