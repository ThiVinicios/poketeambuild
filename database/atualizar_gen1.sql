-- Migração segura para limitar o banco existente aos tipos da 1ª geração.
-- Execute uma única vez no banco teambuild, sem apagar usuários ou equipes.
START TRANSACTION;

-- Corrige os Pokémon que receberam tipagens introduzidas depois da 1ª geração.
DELETE pt FROM pokemon_tipos pt
JOIN tipos t ON t.id = pt.tipo_id
JOIN pokemon p ON p.id = pt.pokemon_id
WHERE (t.slug = 'fairy' AND p.numero NOT IN (35, 36))
   OR t.slug IN ('steel', 'dark');

-- Clefairy e Clefable eram Normal na 1ª geração.
INSERT IGNORE INTO pokemon_tipos (pokemon_id, tipo_id, ordem)
SELECT p.id, normal.id, 1
FROM pokemon p
JOIN tipos normal ON normal.slug = 'normal'
WHERE p.numero IN (35, 36);

-- Magnemite e Magneton eram somente Elétrico na 1ª geração.
DELETE pt FROM pokemon_tipos pt
JOIN pokemon p ON p.id = pt.pokemon_id
JOIN tipos t ON t.id = pt.tipo_id
WHERE p.numero IN (81, 82) AND t.slug = 'steel';

-- Clefairy, Clefable e Mr. Mime não tinham tipo Fada na 1ª geração.
DELETE pt FROM pokemon_tipos pt
JOIN pokemon p ON p.id = pt.pokemon_id
JOIN tipos t ON t.id = pt.tipo_id
WHERE p.numero IN (35, 36, 122) AND t.slug = 'fairy';

-- Remove definitivamente as tipagens posteriores à 1ª geração.
DELETE FROM tipos WHERE slug IN ('steel', 'fairy', 'dark');

-- Melhora a resolução apenas dos sprites PokeAPI padrão; URLs personalizadas são preservadas.
UPDATE pokemon
SET imagem_url = REPLACE(
  imagem_url,
  '/sprites/pokemon/',
  '/sprites/pokemon/other/official-artwork/'
)
WHERE imagem_url LIKE 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/%'
  AND imagem_url NOT LIKE '%/other/official-artwork/%';

COMMIT;
