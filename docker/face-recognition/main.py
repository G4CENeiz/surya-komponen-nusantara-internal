"""
Face Recognition Microservice
FastAPI application using DeepFace for face verification
"""

import io
import base64
import tempfile
from pathlib import Path

from fastapi import FastAPI, HTTPException, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from deepface import DeepFace

app = FastAPI(
    title="Face Recognition Service",
    description="Face verification and recognition using DeepFace",
    version="1.0.0",
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Models directory for storing face embeddings
MODELS_DIR = Path("/app/models")
MODELS_DIR.mkdir(exist_ok=True)


class VerifyRequest(BaseModel):
    """Request model for face verification using base64 images."""
    image1: str  # base64 encoded image
    image2: str  # base64 encoded image
    model_name: str = "VGG-Face"
    distance_metric: str = "cosine"


class VerifyResponse(BaseModel):
    """Response model for face verification."""
    verified: bool
    distance: float
    confidence: float
    model: str
    threshold: float


class EmbeddingRequest(BaseModel):
    """Request model for computing face embedding."""
    image: str  # base64 encoded image
    model_name: str = "VGG-Face"


class EmbeddingResponse(BaseModel):
    """Response model for face embedding."""
    embedding: list[float]
    model: str
    face_detected: bool


class HealthResponse(BaseModel):
    """Health check response."""
    status: str
    models_loaded: bool


def save_temp_image(base64_image: str) -> str:
    """Save base64 image to a temporary file and return the path."""
    # Remove data URL prefix if present
    if "," in base64_image:
        base64_image = base64_image.split(",")[1]

    image_data = base64.b64decode(base64_image)

    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as tmp:
        tmp.write(image_data)
        return tmp.name


@app.get("/health", response_model=HealthResponse)
async def health_check():
    """Health check endpoint."""
    return HealthResponse(status="healthy", models_loaded=True)


@app.post("/verify", response_model=VerifyResponse)
async def verify_face(request: VerifyRequest):
    """
    Verify if two faces belong to the same person.

    Accepts two base64-encoded images and returns a verification result.
    """
    try:
        # Save images to temp files
        img1_path = save_temp_image(request.image1)
        img2_path = save_temp_image(request.image2)

        # Perform face verification
        result = DeepFace.verify(
            img1_path=img1_path,
            img2_path=img2_path,
            model_name=request.model_name,
            distance_metric=request.distance_metric,
            enforce_detection=True,
        )

        # Cleanup temp files
        Path(img1_path).unlink(missing_ok=True)
        Path(img2_path).unlink(missing_ok=True)

        # Get threshold for the model
        threshold = DeepFace.find_threshold(
            request.model_name, request.distance_metric
        )

        return VerifyResponse(
            verified=result["verified"],
            distance=result["distance"],
            confidence=1 - result["distance"],  # Convert distance to confidence
            model=request.model_name,
            threshold=threshold,
        )

    except ValueError as e:
        raise HTTPException(status_code=400, detail=f"Face detection failed: {str(e)}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Verification failed: {str(e)}")


@app.post("/embedding", response_model=EmbeddingResponse)
async def get_embedding(request: EmbeddingRequest):
    """
    Compute face embedding for a single image.

    Returns a 128-dimensional vector representing the face.
    """
    try:
        # Save image to temp file
        img_path = save_temp_image(request.image)

        # Compute embedding
        embedding_objs = DeepFace.represent(
            img_path=img_path,
            model_name=request.model_name,
            enforce_detection=True,
        )

        # Cleanup temp file
        Path(img_path).unlink(missing_ok=True)

        if not embedding_objs:
            return EmbeddingResponse(
                embedding=[],
                model=request.model_name,
                face_detected=False,
            )

        return EmbeddingResponse(
            embedding=embedding_objs[0]["embedding"],
            model=request.model_name,
            face_detected=True,
        )

    except ValueError as e:
        raise HTTPException(status_code=400, detail=f"Face detection failed: {str(e)}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Embedding failed: {str(e)}")


@app.post("/verify-file", response_model=VerifyResponse)
async def verify_face_file(
    file1: UploadFile = File(...),
    file2: UploadFile = File(...),
    model_name: str = "VGG-Face",
    distance_metric: str = "cosine",
):
    """
    Verify if two face files belong to the same person.

    Accepts two uploaded image files.
    """
    try:
        # Read files
        img1_bytes = await file1.read()
        img2_bytes = await file2.read()

        # Save to temp files
        with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as tmp1:
            tmp1.write(img1_bytes)
            img1_path = tmp1.name

        with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as tmp2:
            tmp2.write(img2_bytes)
            img2_path = tmp2.name

        # Perform verification
        result = DeepFace.verify(
            img1_path=img1_path,
            img2_path=img2_path,
            model_name=model_name,
            distance_metric=distance_metric,
            enforce_detection=True,
        )

        # Cleanup
        Path(img1_path).unlink(missing_ok=True)
        Path(img2_path).unlink(missing_ok=True)

        threshold = DeepFace.find_threshold(model_name, distance_metric)

        return VerifyResponse(
            verified=result["verified"],
            distance=result["distance"],
            confidence=1 - result["distance"],
            model=model_name,
            threshold=threshold,
        )

    except ValueError as e:
        raise HTTPException(status_code=400, detail=f"Face detection failed: {str(e)}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Verification failed: {str(e)}")


@app.post("/embedding-file", response_model=EmbeddingResponse)
async def get_embedding_file(
    file: UploadFile = File(...),
    model_name: str = "VGG-Face",
):
    """
    Compute face embedding for an uploaded image file.
    """
    try:
        file_bytes = await file.read()

        with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as tmp:
            tmp.write(file_bytes)
            img_path = tmp.name

        embedding_objs = DeepFace.represent(
            img_path=img_path,
            model_name=model_name,
            enforce_detection=True,
        )

        Path(img_path).unlink(missing_ok=True)

        if not embedding_objs:
            return EmbeddingResponse(
                embedding=[],
                model=model_name,
                face_detected=False,
            )

        return EmbeddingResponse(
            embedding=embedding_objs[0]["embedding"],
            model=model_name,
            face_detected=True,
        )

    except ValueError as e:
        raise HTTPException(status_code=400, detail=f"Face detection failed: {str(e)}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Embedding failed: {str(e)}")
