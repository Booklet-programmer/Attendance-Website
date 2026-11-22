<?php
declare(strict_types=1);

/**
 * Durable session storage for stateless Vercel container instances.
 * Session data is kept in PostgreSQL rather than a local container filesystem.
 */
final class PostgresSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT session_data FROM app_sessions WHERE session_id = :id AND expires_at > NOW()'
        );
        $stmt->execute(['id' => $id]);
        $value = $stmt->fetchColumn();
        return $value === false ? '' : (string)$value;
    }

    public function write(string $id, string $data): bool
    {
        $ttl = (int)ini_get('session.gc_maxlifetime');
        $stmt = $this->pdo->prepare(
            'INSERT INTO app_sessions(session_id, session_data, expires_at)
             VALUES(:id, :data, NOW() + (:ttl * INTERVAL \'1 second\'))
             ON CONFLICT(session_id) DO UPDATE
             SET session_data = EXCLUDED.session_data, expires_at = EXCLUDED.expires_at'
        );
        return $stmt->execute(['id' => $id, 'data' => $data, 'ttl' => $ttl]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM app_sessions WHERE session_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM app_sessions WHERE expires_at < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}
