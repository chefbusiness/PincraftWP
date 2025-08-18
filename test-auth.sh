#!/bin/bash

echo "Testing authentication..."
curl -X GET https://pincraftwp-production.up.railway.app/api/v1/account/usage \
  -H "Content-Type: application/json" \
  -H "x-api-key: pk_test_deed55b71577916257060f2e" \
  -v