require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');

// Test database connection first
const db = require('./config/database');
console.log('📊 Database connection initialized');

// Import routes
const authRoutes = require('./routes/auth');
const pinsRoutes = require('./routes/pins');
const accountRoutes = require('./routes/account');
const webhookRoutes = require('./routes/webhooks');

const app = express();

// Security middleware
app.use(helmet());
app.use(cors({
  origin: process.env.NODE_ENV === 'production' 
    ? ['https://pincraftwp.com', /\.wordpress\.com$/] 
    : '*',
  credentials: true
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 15 * 60 * 1000,
  max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100,
  message: 'Too many requests from this IP, please try again later.'
});

app.use('/api/', limiter);

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Stripe webhook needs raw body
app.use('/api/v1/webhooks/stripe', express.raw({ type: 'application/json' }));

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    timestamp: new Date().toISOString(),
    environment: process.env.NODE_ENV 
  });
});

// API Routes
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/pins', pinsRoutes);
app.use('/api/v1/account', accountRoutes);
app.use('/api/v1/webhooks', webhookRoutes);

// 404 handler
app.use((req, res) => {
  res.status(404).json({ 
    error: 'Endpoint not found',
    path: req.path 
  });
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('Error:', err);
  
  const status = err.status || 500;
  const message = err.message || 'Internal server error';
  
  res.status(status).json({
    error: message,
    ...(process.env.NODE_ENV === 'development' && { stack: err.stack })
  });
});

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 PincraftWP API running on port ${PORT}`);
  console.log(`📍 Environment: ${process.env.NODE_ENV}`);
});