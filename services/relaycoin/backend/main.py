import uvicorn
import uvloop

from app.main_app import configure_application

uvloop.install()

app = configure_application()

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="debug")
