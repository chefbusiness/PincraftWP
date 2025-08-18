const express = require('express');
const router = express.Router();
const db = require('../config/database');
const { authenticateApiKey } = require('../middleware/auth');

// Todas las rutas requieren autenticación
router.use(authenticateApiKey);

// Obtener información de uso y créditos
router.get('/usage', async (req, res) => {
  try {
    const userId = req.user.id;

    // Obtener información del usuario
    const userResult = await db.query(
      `SELECT email, plan_type, monthly_credits, credits_used, reset_date, created_at
       FROM users WHERE id = $1`,
      [userId]
    );

    const user = userResult.rows[0];

    // Calcular días hasta reset
    const resetDate = new Date(user.reset_date);
    const now = new Date();
    const daysUntilReset = 30 - Math.floor((now - resetDate) / (1000 * 60 * 60 * 24));

    // Obtener estadísticas de uso
    const statsResult = await db.query(
      `SELECT 
        COUNT(*) as total_generations,
        SUM(pin_count) as total_pins,
        AVG(pin_count) as avg_pins_per_generation
       FROM pin_generations
       WHERE user_id = $1 AND created_at >= $2`,
      [userId, user.reset_date]
    );

    const stats = statsResult.rows[0];

    res.json({
      success: true,
      account: {
        email: user.email,
        plan: user.plan_type,
        member_since: user.created_at
      },
      usage: {
        monthly_limit: user.monthly_credits,
        credits_used: user.credits_used,
        credits_remaining: user.monthly_credits - user.credits_used,
        reset_date: user.reset_date,
        days_until_reset: Math.max(0, daysUntilReset)
      },
      statistics: {
        total_generations: parseInt(stats.total_generations) || 0,
        total_pins: parseInt(stats.total_pins) || 0,
        average_pins_per_generation: parseFloat(stats.avg_pins_per_generation) || 0
      }
    });

  } catch (error) {
    console.error('Get usage error:', error);
    res.status(500).json({ 
      error: 'Failed to fetch usage information' 
    });
  }
});

// Obtener planes disponibles
router.get('/plans', async (req, res) => {
  try {
    const result = await db.query(
      `SELECT name, display_name, monthly_credits, price_cents, features
       FROM plans 
       WHERE is_active = true
       ORDER BY price_cents ASC`
    );

    const plans = result.rows.map(plan => ({
      ...plan,
      price: plan.price_cents / 100,
      currency: 'USD'
    }));

    res.json({
      success: true,
      plans
    });

  } catch (error) {
    console.error('Get plans error:', error);
    res.status(500).json({ 
      error: 'Failed to fetch plans' 
    });
  }
});

// Actualizar plan (integración con Stripe vendría aquí)
router.post('/upgrade', async (req, res) => {
  try {
    const { plan } = req.body;
    const userId = req.user.id;

    // Verificar que el plan existe
    const planResult = await db.query(
      'SELECT * FROM plans WHERE name = $1 AND is_active = true',
      [plan]
    );

    if (planResult.rows.length === 0) {
      return res.status(400).json({ 
        error: 'Invalid plan selected' 
      });
    }

    const selectedPlan = planResult.rows[0];

    // Aquí iría la integración con Stripe para procesar el pago
    // Por ahora, solo actualizamos el plan directamente (para testing)

    await db.query(
      `UPDATE users 
       SET plan_type = $1, 
           monthly_credits = $2,
           updated_at = CURRENT_TIMESTAMP
       WHERE id = $3`,
      [plan, selectedPlan.monthly_credits, userId]
    );

    res.json({
      success: true,
      message: 'Plan updated successfully',
      new_plan: plan,
      monthly_credits: selectedPlan.monthly_credits
    });

  } catch (error) {
    console.error('Upgrade plan error:', error);
    res.status(500).json({ 
      error: 'Failed to upgrade plan' 
    });
  }
});

module.exports = router;