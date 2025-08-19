const express = require('express');
const router = express.Router();
const { body, query } = require('express-validator');
const pinsController = require('../controllers/pinsController');
const { authenticateApiKey } = require('../middleware/auth');

// Validaciones
const validateGenerate = [
  body('title').notEmpty().withMessage('Title is required'),
  body('domain').notEmpty().withMessage('Domain is required'),
  body('count').optional().isInt({ min: 1, max: 10 }),
  body('style').optional().isIn(['modern', 'vibrant', 'elegant', 'playful', 'professional'])
];

// Todas las rutas requieren autenticación con API Key
router.use(authenticateApiKey);

// Debug endpoint temporal
router.get('/debug-config', (req, res) => {
  const envCheck = {
    hasOpenAI: !!process.env.OPENAI_API_KEY,
    hasReplicate: !!process.env.REPLICATE_API_TOKEN,
    openaiKeyPrefix: process.env.OPENAI_API_KEY ? process.env.OPENAI_API_KEY.substring(0, 8) + '...' : 'MISSING',
    replicateKeyPrefix: process.env.REPLICATE_API_TOKEN ? process.env.REPLICATE_API_TOKEN.substring(0, 8) + '...' : 'MISSING',
    nodeEnv: process.env.NODE_ENV,
    timestamp: new Date().toISOString()
  };
  res.json(envCheck);
});

// Test simple de OpenAI
router.get('/test-openai', async (req, res) => {
  try {
    const OpenAI = require('openai');
    const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
    
    const completion = await openai.chat.completions.create({
      model: "gpt-4o-mini",
      messages: [{ role: "user", content: "Say 'test successful'" }],
      max_tokens: 10
    });
    
    res.json({ 
      success: true, 
      response: completion.choices[0].message.content,
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    res.json({ 
      success: false, 
      error: error.message,
      errorType: error.constructor.name,
      timestamp: new Date().toISOString()
    });
  }
});

// Generar pines
router.post('/generate', validateGenerate, pinsController.generatePins);

// Obtener historial
router.get('/history', pinsController.getGenerationHistory);

// Obtener detalles de una generación
router.get('/generation/:id', pinsController.getGenerationDetails);

module.exports = router;