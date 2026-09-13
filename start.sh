#!/bin/bash
# ERP - Start Script for any company
# Usage: ./start.sh

echo "Starting Novera ERP..."
echo ""

# Kill any existing servers
lsof -ti:8000 | xargs kill -9 2>/dev/null || true
lsof -ti:5173 | xargs kill -9 2>/dev/null || true
sleep 1

# Start Laravel (suppress PHP 8.5 deprecation warnings)
echo "Starting Laravel API on http://localhost:8000 ..."
php -d error_reporting=22527 -d display_errors=Off artisan serve --host=127.0.0.1 --port=8000 &>/tmp/laravel-erp.log &
LARAVEL_PID=$!
sleep 2

# Verify Laravel is running
if curl -s http://localhost:8000/api/health 2>/dev/null | grep -q "ok" || curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/ | grep -q "200\|302"; then
  echo "  Laravel API running (PID: $LARAVEL_PID)"
else
  echo "  Laravel failed to start. Check /tmp/laravel-erp.log"
  exit 1
fi

# Start Vite frontend
echo "Starting Frontend on http://localhost:5173 ..."
cd frontend && npx vite --host 127.0.0.1 --port 5173 &>/tmp/vite-erp.log &
VITE_PID=$!
sleep 3

if curl -s -o /dev/null -w "%{http_code}" http://localhost:5173 | grep -q "200"; then
  echo "  Frontend running (PID: $VITE_PID)"
else
  echo "  Vite failed to start. Check /tmp/vite-erp.log"
fi

echo ""
echo "============================================"
echo "  Novera ERP is READY"
echo "============================================"
echo ""
echo "  Frontend:  http://localhost:5173"
echo "  Backend:   http://localhost:8000"
echo ""
echo "  Set ADMIN_EMAIL / ADMIN_PASSWORD env vars to override defaults."
echo ""
echo "  Stop:      kill $LARAVEL_PID $VITE_PID"
echo "============================================"
