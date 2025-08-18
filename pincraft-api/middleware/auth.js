const jwt = require('jsonwebtoken');
const db = require('../config/database');

// Middleware para autenticación con JWT
exports.authenticate = async (req, res, next) => {
  try {
    const token = req.headers.authorization?.replace('Bearer ', '');

    if (!token) {
      return res.status(401).json({ 
        error: 'Authentication required' 
      });
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    const result = await db.query(
      'SELECT id, email, plan_type FROM users WHERE id = $1 AND is_active = true',
      [decoded.id]
    );

    if (result.rows.length === 0) {
      return res.status(401).json({ 
        error: 'User not found or inactive' 
      });
    }

    req.user = result.rows[0];
    next();

  } catch (error) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({ 
        error: 'Invalid token' 
      });
    }
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({ 
        error: 'Token expired' 
      });
    }
    
    res.status(500).json({ 
      error: 'Authentication failed' 
    });
  }
};

// Middleware para autenticación con API Key (usado por el plugin)
exports.authenticateApiKey = async (req, res, next) => {
  try {
    console.log('🔐 Auth middleware - Headers:', Object.keys(req.headers));
    console.log('🔐 Auth middleware - x-api-key:', req.headers['x-api-key']);
    console.log('🔐 Auth middleware - Path:', req.path);
    console.log('🔐 Auth middleware - Method:', req.method);

    const apiKey = req.headers['x-api-key'];

    if (!apiKey) {
      console.log('❌ No API key provided');
      return res.status(401).json({ 
        error: 'API key required' 
      });
    }

    console.log('🔍 Looking up API key:', apiKey);

    const result = await db.query(
      `SELECT id, email, plan_type, monthly_credits, credits_used, reset_date
       FROM users 
       WHERE api_key = $1 AND is_active = true`,
      [apiKey]
    );

    console.log('📊 Database query result:', result.rows.length, 'rows');

    if (result.rows.length === 0) {
      console.log('❌ Invalid API key or inactive user');
      return res.status(401).json({ 
        error: 'Invalid API key' 
      });
    }

    const user = result.rows[0];

    // Verificar si necesita reset de créditos
    const resetDate = new Date(user.reset_date);
    const now = new Date();
    const daysSinceReset = Math.floor((now - resetDate) / (1000 * 60 * 60 * 24));

    if (daysSinceReset >= 30) {
      await db.query(
        'UPDATE users SET credits_used = 0, reset_date = CURRENT_TIMESTAMP WHERE id = $1',
        [user.id]
      );
      user.credits_used = 0;
    }

    // Verificar créditos disponibles
    if (user.credits_used >= user.monthly_credits) {
      return res.status(403).json({ 
        error: 'Monthly credit limit exceeded',
        credits_used: user.credits_used,
        monthly_limit: user.monthly_credits,
        reset_date: user.reset_date
      });
    }

    console.log('✅ Authentication successful for user:', user.email);
    req.user = user;
    next();

  } catch (error) {
    console.error('❌ API key authentication error:', error);
    console.error('❌ Error stack:', error.stack);
    res.status(500).json({ 
      error: 'Authentication failed',
      details: error.message 
    });
  }
};

// Middleware para verificar plan específico
exports.requirePlan = (allowedPlans) => {
  return (req, res, next) => {
    if (!allowedPlans.includes(req.user.plan_type)) {
      return res.status(403).json({ 
        error: 'Your plan does not have access to this feature',
        current_plan: req.user.plan_type,
        required_plans: allowedPlans
      });
    }
    next();
  };
};