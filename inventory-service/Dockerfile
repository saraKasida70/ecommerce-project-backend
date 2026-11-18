
FROM python:3.12-slim

WORKDIR /app

COPY requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

COPY proto/ ./proto/
COPY src/ ./src/

# Generate Python files from the Protocol Buffers contract
RUN python -m grpc_tools.protoc \
    -I./proto \
    --python_out=./src \
    --grpc_python_out=./src \
    ./proto/inventory.proto

EXPOSE 50051

CMD ["python", "src/server.py"]
