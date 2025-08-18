#!/bin/bash

# Test PincraftWP API - Generate Pin
curl -X POST https://pincraftwp-production.up.railway.app/api/v1/pins/generate \
  -H "Content-Type: application/json" \
  -H "x-api-key: pk_test_deed55b71577916257060f2e" \
  -d '{
    "title": "Deliciosa Receta de Pasta Italiana",
    "content": "Una increíble receta de pasta con ingredientes frescos y sabores auténticos de la cocina italiana. Perfecta para una cena romántica o familiar.",
    "domain": "michef.com",
    "count": 1
  }'