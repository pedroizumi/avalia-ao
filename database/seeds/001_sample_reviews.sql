INSERT INTO reviews
  (evaluation_uuid, vendor_id, rating, comment, ip_hash, user_agent_hash, client_token_hash, session_id_hash, created_at)
VALUES
  ('00000000-0000-4000-8000-000000000001', (SELECT id FROM vendors WHERE slug = 'nicolas'), 5, 'Atendimento excelente, muito atencioso.', REPEAT('a', 64), REPEAT('b', 64), REPEAT('c', 64), REPEAT('d', 64), NOW() - INTERVAL 6 DAY),
  ('00000000-0000-4000-8000-000000000002', (SELECT id FROM vendors WHERE slug = 'nicolas'), 5, 'Explicou tudo com clareza.', REPEAT('e', 64), REPEAT('f', 64), REPEAT('1', 64), REPEAT('2', 64), NOW() - INTERVAL 5 DAY),
  ('00000000-0000-4000-8000-000000000003', (SELECT id FROM vendors WHERE slug = 'nicolas'), 4, 'Foi rápido e resolveu minhas dúvidas.', REPEAT('3', 64), REPEAT('4', 64), REPEAT('5', 64), REPEAT('6', 64), NOW() - INTERVAL 4 DAY),
  ('00000000-0000-4000-8000-000000000004', (SELECT id FROM vendors WHERE slug = 'nicolas'), 5, NULL, REPEAT('7', 64), REPEAT('8', 64), REPEAT('9', 64), REPEAT('0', 64), NOW() - INTERVAL 3 DAY),
  ('00000000-0000-4000-8000-000000000005', (SELECT id FROM vendors WHERE slug = 'gabriel'), 5, 'Muito educado e objetivo.', REPEAT('a1', 32), REPEAT('b1', 32), REPEAT('c1', 32), REPEAT('d1', 32), NOW() - INTERVAL 6 DAY),
  ('00000000-0000-4000-8000-000000000006', (SELECT id FROM vendors WHERE slug = 'gabriel'), 4, 'Bom atendimento.', REPEAT('e1', 32), REPEAT('f1', 32), REPEAT('a2', 32), REPEAT('b2', 32), NOW() - INTERVAL 4 DAY),
  ('00000000-0000-4000-8000-000000000007', (SELECT id FROM vendors WHERE slug = 'gabriel'), 4, 'Gostei da experiência.', REPEAT('c2', 32), REPEAT('d2', 32), REPEAT('e2', 32), REPEAT('f2', 32), NOW() - INTERVAL 2 DAY),
  ('00000000-0000-4000-8000-000000000008', (SELECT id FROM vendors WHERE slug = 'gabriel'), 5, NULL, REPEAT('a3', 32), REPEAT('b3', 32), REPEAT('c3', 32), REPEAT('d3', 32), NOW() - INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE
  rating = VALUES(rating),
  comment = VALUES(comment);

