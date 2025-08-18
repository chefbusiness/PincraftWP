const express = require('express');
const router = express.Router();

// Webhook de Stripe (para futura implementación)
router.post('/stripe', express.raw({ type: 'application/json' }), async (req, res) => {
  try {
    // Aquí iría la lógica de webhooks de Stripe
    // para manejar suscripciones, pagos, etc.
    
    res.json({ received: true });
  } catch (error) {
    console.error('Stripe webhook error:', error);
    res.status(400).json({ error: 'Webhook processing failed' });
  }
});

module.exports = router;