"""Passenger WSGI entry point for Hostinger shared hosting Python apps.

Hostinger looks for passenger_wsgi.py in the app folder and imports
`application`. Point the Hostinger Python app at this folder, set the
environment variables (SECRET_KEY, FLASK_ENV=production), and set the
domain redwayacademy.com to the app. No code change is needed here.
"""
import os
import sys

# Make sure the app folder is on the import path.
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import app as application  # noqa: F401
