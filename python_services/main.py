from fastapi import FastAPI
from pydantic import BaseModel
import numpy as np
from sklearn.cluster import KMeans
import os
from dotenv import load_dotenv
from typing import Any
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_core.prompts import PromptTemplate

load_dotenv(dotenv_path="../.env")

api_key = os.getenv("GEMINI_API_KEY")

if not api_key:
    raise ValueError("GEMINI_API_KEY unknown value at .env!")

llm = ChatGoogleGenerativeAI(
    model="gemini-2.5-flash",
    temperature=0.3,
    google_api_key=api_key
)

app = FastAPI()


#prepare the data  
X_train = np.array ([
    [70, 700, 15, 8], #posession dominant
    [35, 300, 12, 15], #Lethal Counter-Attack
    [50, 450, 10, 10], #Balanced Transition
    [25, 200, 3, 12] #Defensive Low-Block
])

kmeans = KMeans(n_clusters=4, random_state=42, n_init=10).fit(X_train)

# Mapping cluster results to playing style names.
labels_map = {
    kmeans.predict([[70, 700, 15, 8]])[0]: "Posession Dominant 🎯",
    kmeans.predict([[35, 300, 12, 15]])[0]: "Lethal counter attack",
    kmeans.predict([[50, 450, 10, 10]])[0]: "Balanced Transition",
    kmeans.predict([[25, 200, 3, 12]])[0]: "Defensive"

}

# Data receiver structure
class TeamStats(BaseModel):
    possession: float
    passes: float
    shots: float
    fouls: float

# API Endpoint to be called 
@app.post("/predict-playstyle")
def predict_playstyle(stats: TeamStats):
    # Edit from laravel to Numpy
    features = np.array([[stats.possession, stats.passes, stats.shots, stats.fouls]])

    # Predict which cluster it belongs to
    cluster = kmeans.predict(features)[0]

    # Return the value to laravel
    return {"playstyle": labels_map[cluster]}


#agentic AI Chatbot
class ChatRequest(BaseModel):
    question: str
    match_title: str
    stats_context: Any

@app.post("/chat-tactical")
def chat_tactical(request: ChatRequest):

    template = """
    You are a professional football tactical assistant coach.
    Use the following match statistics context from "{match_title}" to answer the user's question.

    Head-to-Head Statistics Context:
    {context}

    Question: {question}

    Instructions:
    - Answer concisely, analytically, and directly (maximum 3 paragraphs).
    - If the question is not related to this match or football tactics, politely decline to answer.
    - Provide your response in professional English.

    Answer:
    """

    prompt = PromptTemplate(
        input_variables=["match_title", "context", "question"],
        template=template
    )

    chain = prompt | llm
    try:
        #execute AI
        response = chain.invoke({
            "match_title": request.match_title,
            "context": str(request.stats_context),
            "question": request.question
        })

        return {"answer": response.content}
    except Exception as e:
        return {"answer": f"Sorry, The tactical assistant is experiencing technical difficulties: {str(e)}"}