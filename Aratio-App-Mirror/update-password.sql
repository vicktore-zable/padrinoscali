-- Actualizar password del administrador
UPDATE usuarios
SET password = '$2y$10$Sp5Jkd23CbvN3fNybD327eyowgEO7OlRxloZrukNh5oQ5qWjRjG9i',
    email = 'admin@aratio.mrmtech.net'
WHERE id = 1;

-- Verificar
SELECT id, nombre, email, 'Password actualizado' as status FROM usuarios WHERE id = 1;
