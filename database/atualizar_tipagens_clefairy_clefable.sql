-- Correção pontual da tipagem de Clefairy e Clefable na 1ª geração.
-- Pode ser executado em banco já existente; não altera equipes nem usuários.
START TRANSACTION;

DELETE pt
FROM pokemon_tipos pt
JOIN pokemon p ON p.id = pt.pokemon_id
WHERE p.numero IN (35, 36);

INSERT INTO pokemon_tipos (pokemon_id, tipo_id, ordem)
SELECT p.id, t.id, 1
FROM pokemon p
JOIN tipos t ON t.slug = 'normal'
WHERE p.numero IN (35, 36);

COMMIT;
