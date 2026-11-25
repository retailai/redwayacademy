"""
Redway Academy — production configuration.

All secrets come from environment variables. Never commit a real
SECRET_KEY or a real database password. Copy .env.example to .env
for local development only; on Hostinger set the same variables in
the Python app / environment settings instead.
"""
import os
from datetime import timedelta

BASE_DIR = os.path.dirname(os.path.abspath(__file__))


class Config:
    # Secret key: set SECRET_KEY in the environment in production.
    # The dev fallback is only for local testing and must never be
    # used in production — app.py logs a warning when it is in use.
    SECRET_KEY = os.environ.get("SECRET_KEY", "redway-dev-secret-change-me")

    # FLASK_ENV=production turns DEBUG off and makes cookies secure.
    ENV = os.environ.get("FLASK_ENV", "development")
    DEBUG = ENV != "production"

    # Sessions: 8 hours, HTTP-only, SameSite=Lax, Secure in production.
    PERMANENT_SESSION_LIFETIME = timedelta(hours=8)
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = "Lax"
    SESSION_COOKIE_SECURE = ENV == "production"

    # Uploads: 8 MB max, only PDF / JPG / PNG (checked in app.py too).
    MAX_CONTENT_LENGTH = int(os.environ.get("MAX_UPLOAD_MB", "8")) * 1024 * 1024
    UPLOAD_FOLDER = os.environ.get(
        "UPLOAD_FOLDER", os.path.join(BASE_DIR, "instance", "uploads")
    )

    # Database: SQLite file. Set DATABASE_PATH in production if the
    # host wants the file outside the app folder.
    DATABASE = os.environ.get(
        "DATABASE_PATH", os.path.join(BASE_DIR, "instance", "redway.db")
    )

    # Logging: a file under instance/logs/ in production, console in dev.
    LOG_FILE = os.environ.get(
        "LOG_FILE", os.path.join(BASE_DIR, "instance", "logs", "redway.log")
    )


class ProductionConfig(Config):
    ENV = "production"
    DEBUG = False
    SESSION_COOKIE_SECURE = True


class DevelopmentConfig(Config):
    ENV = "development"
    DEBUG = True
    SESSION_COOKIE_SECURE = False
