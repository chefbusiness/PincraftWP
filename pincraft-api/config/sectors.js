/**
 * Configuración de sectores para PincraftWP
 * Cada sector tiene prompts optimizados para OpenAI e Ideogram
 */

const SECTORS = {
  'decoracion_hogar': {
    id: 'decoracion_hogar',
    name: 'Decoración del Hogar y DIY',
    emoji: '🏠',
    description: 'Decoración, organización, proyectos DIY',
    openai: {
      system: 'Eres experto en decoración de interiores y DIY. Crea contenido inspiracional que motive a transformar espacios.',
      tone: 'inspiracional, acogedor, práctico',
      keywords: ['decoración', 'hogar', 'DIY', 'organización', 'estilo']
    },
    ideogram: {
      base: 'Interior design photography, home decor, cozy atmosphere',
      style: 'warm lighting, magazine quality, Pinterest aesthetic, inviting spaces',
      elements: 'furniture, textures, colors, natural light'
    },
    hashtags: ['#HomeDecor', '#DIYHome', '#InteriorDesign', '#CozyHome', '#DecorIdeas']
  },
  
  'recetas_comida': {
    id: 'recetas_comida',
    name: 'Recetas y Comida',
    emoji: '🍲',
    description: 'Recetas, cocina, gastronomía',
    openai: {
      system: 'Eres chef experto y food blogger. Crea contenido apetitoso que inspire a cocinar.',
      tone: 'apetitoso, fácil de seguir, entusiasta',
      keywords: ['receta', 'cocina', 'ingredientes', 'sabor', 'delicioso']
    },
    ideogram: {
      base: 'Food photography, delicious cuisine, recipe presentation',
      style: 'overhead shot, vibrant colors, appetizing, professional food styling',
      elements: 'ingredients, finished dish, cooking process, garnishes'
    },
    hashtags: ['#Recetas', '#Foodie', '#CocinaFacil', '#ComidaCasera', '#RecetasRapidas']
  },
  
  'moda_femenina': {
    id: 'moda_femenina',
    name: 'Moda Femenina',
    emoji: '👗',
    description: 'Outfits, tendencias, estilo personal',
    openai: {
      system: 'Eres estilista y experta en moda. Crea contenido de moda aspiracional y accesible.',
      tone: 'elegante, trendy, empoderador',
      keywords: ['moda', 'estilo', 'outfit', 'tendencia', 'look']
    },
    ideogram: {
      base: 'Fashion photography, outfit styling, trendy clothes',
      style: 'magazine editorial, clean background, professional lighting, stylish',
      elements: 'clothing, accessories, shoes, fashion details'
    },
    hashtags: ['#Fashion', '#OOTD', '#Style', '#ModaFemenina', '#Tendencias']
  },
  
  'belleza_cuidado': {
    id: 'belleza_cuidado',
    name: 'Belleza y Cuidado Personal',
    emoji: '💄',
    description: 'Maquillaje, skincare, tutoriales',
    openai: {
      system: 'Eres experta en belleza y cuidado personal. Crea contenido educativo y aspiracional.',
      tone: 'experto, amigable, transformador',
      keywords: ['belleza', 'maquillaje', 'skincare', 'cuidado', 'rutina']
    },
    ideogram: {
      base: 'Beauty photography, makeup artistry, skincare products',
      style: 'soft lighting, clean aesthetic, professional beauty shot, glamorous',
      elements: 'cosmetics, beauty tools, skin texture, makeup looks'
    },
    hashtags: ['#Beauty', '#Makeup', '#Skincare', '#BeautyTips', '#GlowUp']
  },
  
  'bodas_eventos': {
    id: 'bodas_eventos',
    name: 'Bodas y Eventos',
    emoji: '👰',
    description: 'Bodas, celebraciones, decoración de eventos',
    openai: {
      system: 'Eres wedding planner experta. Crea contenido romántico e inspiracional para eventos.',
      tone: 'romántico, elegante, soñador',
      keywords: ['boda', 'celebración', 'decoración', 'evento', 'fiesta']
    },
    ideogram: {
      base: 'Wedding photography, event decoration, celebration setup',
      style: 'romantic lighting, elegant composition, dreamy atmosphere, luxurious',
      elements: 'flowers, decorations, venues, details, celebrations'
    },
    hashtags: ['#Wedding', '#WeddingIdeas', '#EventDecor', '#BridalInspiration', '#Bodas']
  },
  
  'maternidad_bebes': {
    id: 'maternidad_bebes',
    name: 'Maternidad y Bebés',
    emoji: '👶',
    description: 'Embarazo, bebés, crianza, familia',
    openai: {
      system: 'Eres experta en maternidad y crianza. Crea contenido útil y emotivo para madres.',
      tone: 'cálido, informativo, maternal',
      keywords: ['bebé', 'maternidad', 'crianza', 'familia', 'niños']
    },
    ideogram: {
      base: 'Baby photography, nursery decor, parenting moments',
      style: 'soft pastel colors, tender moments, warm lighting, family-friendly',
      elements: 'baby items, nursery, toys, family moments'
    },
    hashtags: ['#Maternidad', '#Baby', '#Motherhood', '#Parenting', '#BabyLove']
  },
  
  'viajes_aventuras': {
    id: 'viajes_aventuras',
    name: 'Viajes y Aventuras',
    emoji: '✈️',
    description: 'Destinos, guías de viaje, aventuras',
    openai: {
      system: 'Eres travel blogger experto. Crea contenido inspirador sobre viajes y destinos.',
      tone: 'aventurero, inspirador, informativo',
      keywords: ['viaje', 'destino', 'aventura', 'explorar', 'turismo']
    },
    ideogram: {
      base: 'Travel photography, destinations, landscapes, adventure',
      style: 'epic vistas, vibrant colors, wanderlust aesthetic, stunning locations',
      elements: 'landmarks, nature, cityscapes, travel moments'
    },
    hashtags: ['#Travel', '#Wanderlust', '#TravelGuide', '#Adventure', '#Viajes']
  },
  
  'fitness_ejercicio': {
    id: 'fitness_ejercicio',
    name: 'Fitness y Ejercicio',
    emoji: '💪',
    description: 'Rutinas, ejercicios, vida saludable',
    openai: {
      system: 'Eres entrenador personal certificado. Crea contenido motivador sobre fitness.',
      tone: 'motivador, energético, profesional',
      keywords: ['fitness', 'ejercicio', 'entrenamiento', 'salud', 'workout']
    },
    ideogram: {
      base: 'Fitness photography, workout demonstration, gym environment',
      style: 'dynamic action, motivational, high energy, athletic aesthetic',
      elements: 'exercise equipment, athletic wear, movement, fitness spaces'
    },
    hashtags: ['#Fitness', '#Workout', '#FitLife', '#GymMotivation', '#HealthyLifestyle']
  },
  
  'salud_bienestar': {
    id: 'salud_bienestar',
    name: 'Salud y Bienestar',
    emoji: '🧘',
    description: 'Wellness, mindfulness, vida saludable',
    openai: {
      system: 'Eres experto en wellness y bienestar. Crea contenido sobre vida equilibrada.',
      tone: 'calmado, inspirador, holístico',
      keywords: ['bienestar', 'salud', 'mindfulness', 'equilibrio', 'wellness']
    },
    ideogram: {
      base: 'Wellness photography, meditation, healthy lifestyle',
      style: 'calming colors, zen aesthetic, natural elements, peaceful atmosphere',
      elements: 'yoga poses, nature, healthy food, spa elements'
    },
    hashtags: ['#Wellness', '#SelfCare', '#Mindfulness', '#HealthyLiving', '#Bienestar']
  },
  
  'negocios_emprendimiento': {
    id: 'negocios_emprendimiento',
    name: 'Negocios y Emprendimiento',
    emoji: '💼',
    description: 'Business, marketing, productividad',
    openai: {
      system: 'Eres business coach experto. Crea contenido profesional sobre negocios.',
      tone: 'profesional, inspirador, práctico',
      keywords: ['negocio', 'emprendimiento', 'marketing', 'éxito', 'productividad']
    },
    ideogram: {
      base: 'Business photography, office setup, professional environment',
      style: 'clean modern aesthetic, minimalist, professional lighting, corporate',
      elements: 'workspace, technology, business tools, office design'
    },
    hashtags: ['#Business', '#Entrepreneur', '#Marketing', '#Success', '#Productivity']
  },
  
  'educacion_aprendizaje': {
    id: 'educacion_aprendizaje',
    name: 'Educación y Aprendizaje',
    emoji: '📚',
    description: 'Estudios, cursos, desarrollo personal',
    openai: {
      system: 'Eres educador experto. Crea contenido educativo e inspirador.',
      tone: 'educativo, claro, motivador',
      keywords: ['aprendizaje', 'educación', 'estudio', 'conocimiento', 'desarrollo']
    },
    ideogram: {
      base: 'Educational content, study setup, learning environment',
      style: 'bright and inspiring, organized aesthetic, academic atmosphere',
      elements: 'books, stationery, study spaces, educational materials'
    },
    hashtags: ['#Education', '#Learning', '#StudyTips', '#Knowledge', '#PersonalGrowth']
  },
  
  'arte_creatividad': {
    id: 'arte_creatividad',
    name: 'Arte y Creatividad',
    emoji: '🎨',
    description: 'Arte, manualidades, proyectos creativos',
    openai: {
      system: 'Eres artista y creativo. Crea contenido inspirador sobre arte y creatividad.',
      tone: 'creativo, inspirador, artístico',
      keywords: ['arte', 'creatividad', 'diseño', 'manualidades', 'DIY']
    },
    ideogram: {
      base: 'Art photography, creative projects, artistic compositions',
      style: 'vibrant colors, artistic lighting, creative chaos, inspiring workspace',
      elements: 'art supplies, creative process, artwork, craft materials'
    },
    hashtags: ['#Art', '#Creative', '#DIYProjects', '#Crafts', '#ArtisticExpression']
  },
  
  'tecnologia_gadgets': {
    id: 'tecnologia_gadgets',
    name: 'Tecnología y Gadgets',
    emoji: '💻',
    description: 'Tech, gadgets, innovación',
    openai: {
      system: 'Eres experto en tecnología. Crea contenido sobre tech y gadgets.',
      tone: 'informativo, moderno, entusiasta',
      keywords: ['tecnología', 'gadgets', 'innovación', 'apps', 'digital']
    },
    ideogram: {
      base: 'Tech photography, gadgets, modern devices, clean setup',
      style: 'minimalist tech aesthetic, modern lighting, futuristic feel',
      elements: 'devices, screens, tech accessories, workspace setup'
    },
    hashtags: ['#Tech', '#Gadgets', '#Innovation', '#TechLife', '#Digital']
  },
  
  'jardin_plantas': {
    id: 'jardin_plantas',
    name: 'Jardín y Plantas',
    emoji: '🌱',
    description: 'Jardinería, plantas, naturaleza',
    openai: {
      system: 'Eres experto en jardinería. Crea contenido sobre plantas y jardines.',
      tone: 'natural, relajante, informativo',
      keywords: ['plantas', 'jardín', 'naturaleza', 'verde', 'cultivo']
    },
    ideogram: {
      base: 'Garden photography, plants, botanical beauty, nature',
      style: 'natural lighting, green aesthetic, organic feel, botanical style',
      elements: 'plants, gardens, flowers, gardening tools'
    },
    hashtags: ['#Plants', '#Gardening', '#PlantLove', '#GreenThumb', '#Nature']
  },
  
  'mascotas_animales': {
    id: 'mascotas_animales',
    name: 'Mascotas y Animales',
    emoji: '🐕',
    description: 'Mascotas, cuidado animal, pet lovers',
    openai: {
      system: 'Eres experto en mascotas. Crea contenido adorable y útil sobre animales.',
      tone: 'adorable, informativo, amigable',
      keywords: ['mascotas', 'perros', 'gatos', 'animales', 'cuidado']
    },
    ideogram: {
      base: 'Pet photography, cute animals, pet lifestyle',
      style: 'heartwarming, playful, bright colors, adorable moments',
      elements: 'pets, pet accessories, animal portraits, pet activities'
    },
    hashtags: ['#Pets', '#DogLove', '#CatLife', '#PetCare', '#AnimalLovers']
  }
};

module.exports = { SECTORS };