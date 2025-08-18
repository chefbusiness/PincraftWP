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

// Generar pines
router.post('/generate', validateGenerate, pinsController.generatePins);

// Obtener historial
router.get('/history', pinsController.getGenerationHistory);

// Obtener detalles de una generación
router.get('/generation/:id', pinsController.getGenerationDetails);

module.exports = router;