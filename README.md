# ⚽ Euro Football AI: Tactical Assistant & Match Analyzer

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)
![FastAPI](https://img.shields.io/badge/FastAPI-005571?style=for-the-badge&logo=fastapi)
![LangChain](https://img.shields.io/badge/LangChain-1C3C3C?style=for-the-badge&logo=chainlink&logoColor=white)
![Scikit-Learn](https://img.shields.io/badge/scikit_learn-F7931E?style=for-the-badge&logo=scikit-learn&logoColor=white)

A modern, full-stack web application that combines live football statistics with advanced Artificial Intelligence. This system utilizes a monolithic repository architecture, bridging a Laravel PHP frontend with a Python-powered Machine Learning and NLP microservice.

## ✨ Key Features

*   **Live Match Statistics:** Fetches real-time football match data, lineups, and events via API-Sports.
*   **Tactical Playstyle Recognition (Unsupervised Learning):** Analyzes team statistics (possession, passes, shots, fouls) using a K-Means Clustering algorithm to automatically label a team's hidden tactical playstyle (e.g., *Lethal Counter-Attack*, *Possession Dominant*).
*   **Agentic AI Tactical Assistant (RAG):** Features an interactive chatbot powered by LangChain and Google Gemini. Users can ask specific tactical questions about the match, and the AI will contextually analyze the game's statistics to provide professional coaching insights.
*   **Man of the Match Predictor:** Automatically calculates and sorts the top 3 performing players based on live match ratings.

## 🏗️ System Architecture

This project uses a **Monorepo** approach:
1.  **Frontend/Backend:** Laravel 11 + Livewire 3 + Tailwind CSS handling the UI/UX and API fetching.
2.  **AI Microservice:** FastAPI (Python) running seamlessly in the background, executing Scikit-Learn clustering and LangChain interactions.

## 🚀 How to Run Locally

### Prerequisites
*   PHP 8.3+ & Composer
*   Python 3.10+
*   Google Gemini API Key
*   API-Sports Key

### 1. Setup the Laravel App
```bash
# Clone the repository
git clone [https://github.com/yourusername/euro-football-ai.git](https://github.com/yourusername/euro-football-ai.git)
cd euro-football-ai

# Install PHP dependencies
composer install

# Setup environment variables
cp .env.example .env
php artisan key:generate

# Add your API Keys in the .env file:
# GEMINI_API_KEY=your_gemini_key
# API_SPORTS_KEY=your_api_sports_key

# Run the Laravel server
php artisan serve