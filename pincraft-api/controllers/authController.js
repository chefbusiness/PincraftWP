const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { v4: uuidv4 } = require('uuid');
const db = require('../config/database');
const { validationResult } = require('express-validator');

// Registrar nuevo usuario
exports.register = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }

    const { email, password, plan = 'free' } = req.body;

    // Verificar si el usuario ya existe
    const existingUser = await db.query(
      'SELECT id FROM users WHERE email = $1',
      [email]
    );

    if (existingUser.rows.length > 0) {
      return res.status(409).json({ 
        error: 'Email already registered' 
      });
    }

    // Hash password
    const salt = await bcrypt.genSalt(10);
    const passwordHash = await bcrypt.hash(password, salt);

    // Generar API Key única
    const apiKey = `pk_${uuidv4().replace(/-/g, '')}`;

    // Obtener límites del plan
    const planData = await db.query(
      'SELECT monthly_credits FROM plans WHERE name = $1',
      [plan]
    );

    const monthlyCredits = planData.rows[0]?.monthly_credits || 10;

    // Crear usuario
    const result = await db.query(
      `INSERT INTO users (email, password_hash, api_key, plan_type, monthly_credits)
       VALUES ($1, $2, $3, $4, $5)
       RETURNING id, email, api_key, plan_type, monthly_credits, created_at`,
      [email, passwordHash, apiKey, plan, monthlyCredits]
    );

    const user = result.rows[0];

    // Generar JWT
    const token = jwt.sign(
      { id: user.id, email: user.email },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRE || '30d' }
    );

    res.status(201).json({
      success: true,
      message: 'User registered successfully',
      data: {
        user: {
          id: user.id,
          email: user.email,
          plan: user.plan_type,
          api_key: user.api_key,
          monthly_credits: user.monthly_credits
        },
        token
      }
    });

  } catch (error) {
    console.error('Register error:', error);
    res.status(500).json({ 
      error: 'Failed to register user' 
    });
  }
};

// Login de usuario
exports.login = async (req, res) => {
  try {
    const { email, password } = req.body;

    // Buscar usuario
    const result = await db.query(
      'SELECT * FROM users WHERE email = $1 AND is_active = true',
      [email]
    );

    if (result.rows.length === 0) {
      return res.status(401).json({ 
        error: 'Invalid credentials' 
      });
    }

    const user = result.rows[0];

    // Verificar password
    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      return res.status(401).json({ 
        error: 'Invalid credentials' 
      });
    }

    // Generar JWT
    const token = jwt.sign(
      { id: user.id, email: user.email },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRE || '30d' }
    );

    res.json({
      success: true,
      data: {
        user: {
          id: user.id,
          email: user.email,
          plan: user.plan_type,
          api_key: user.api_key,
          monthly_credits: user.monthly_credits,
          credits_used: user.credits_used
        },
        token
      }
    });

  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ 
      error: 'Failed to login' 
    });
  }
};

// Validar API Key (usado por el plugin WordPress)
exports.validateApiKey = async (req, res) => {
  try {
    const { api_key } = req.body;

    if (!api_key) {
      return res.status(400).json({ 
        error: 'API key is required' 
      });
    }

    // Buscar usuario por API key
    const result = await db.query(
      `SELECT id, email, plan_type, monthly_credits, credits_used, reset_date, is_active
       FROM users 
       WHERE api_key = $1`,
      [api_key]
    );

    if (result.rows.length === 0) {
      return res.status(401).json({ 
        valid: false,
        error: 'Invalid API key' 
      });
    }

    const user = result.rows[0];

    if (!user.is_active) {
      return res.status(403).json({ 
        valid: false,
        error: 'Account is inactive' 
      });
    }

    // Verificar si necesita reset de créditos (mensual)
    const resetDate = new Date(user.reset_date);
    const now = new Date();
    const daysSinceReset = Math.floor((now - resetDate) / (1000 * 60 * 60 * 24));

    if (daysSinceReset >= 30) {
      // Reset créditos mensuales
      await db.query(
        `UPDATE users 
         SET credits_used = 0, reset_date = CURRENT_TIMESTAMP 
         WHERE id = $1`,
        [user.id]
      );
      user.credits_used = 0;
    }

    const remainingCredits = user.monthly_credits - user.credits_used;

    res.json({
      valid: true,
      plan: user.plan_type,
      email: user.email,
      monthly_credits: user.monthly_credits,
      credits_used: user.credits_used,
      remaining_credits: remainingCredits,
      reset_date: user.reset_date
    });

  } catch (error) {
    console.error('Validate API key error:', error);
    res.status(500).json({ 
      valid: false,
      error: 'Failed to validate API key' 
    });
  }
};

// Regenerar API Key
exports.regenerateApiKey = async (req, res) => {
  try {
    const userId = req.user.id; // Del middleware de autenticación

    // Generar nueva API Key
    const newApiKey = `pk_${uuidv4().replace(/-/g, '')}`;

    // Actualizar en la base de datos
    const result = await db.query(
      `UPDATE users 
       SET api_key = $1, updated_at = CURRENT_TIMESTAMP 
       WHERE id = $2
       RETURNING api_key`,
      [newApiKey, userId]
    );

    res.json({
      success: true,
      message: 'API key regenerated successfully',
      api_key: result.rows[0].api_key
    });

  } catch (error) {
    console.error('Regenerate API key error:', error);
    res.status(500).json({ 
      error: 'Failed to regenerate API key' 
    });
  }
};