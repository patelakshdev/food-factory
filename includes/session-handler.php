<?php
/**
 * Database-backed session handler.
 *
 * On serverless hosts (Vercel via vercel-php, etc.) the local filesystem is
 * ephemeral, so PHP's default file sessions cannot survive cold starts. This
 * handler stores sessions in the `sessions` table instead, which makes login
 * and CSRF state reliable everywhere.
 *
 * Installed from config/app.php via session_set_save_handler().
 */

declare(strict_types=1);

final class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    public function open(string $save_path, string $session_name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    private function pdo(): PDO
    {
        return $this->pdo ??= db();
    }

    public function read(string $id): string
    {
        try {
            $stmt = $this->pdo()->prepare('SELECT payload FROM sessions WHERE session_id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            return $row ? (string)$row['payload'] : '';
        } catch (Throwable $e) {
            error_log('[SESSION READ ERROR] ' . $e->getMessage());
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $stmt = $this->pdo()->prepare(
                'INSERT INTO sessions (session_id, user_id, payload, last_activity, ip_address)
                 VALUES (:id, :uid, :payload, :activity, :ip)
                 ON DUPLICATE KEY UPDATE
                     user_id = VALUES(user_id),
                     payload = VALUES(payload),
                     last_activity = VALUES(last_activity),
                     ip_address = VALUES(ip_address)'
            );
            $stmt->execute([
                'id'       => $id,
                'uid'      => (int)($_SESSION['user_id'] ?? 0),
                'payload'  => $data,
                'activity' => time(),
                'ip'       => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
            ]);
            return true;
        } catch (Throwable $e) {
            error_log('[SESSION WRITE ERROR] ' . $e->getMessage());
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->pdo()->prepare('DELETE FROM sessions WHERE session_id = :id')
                ->execute(['id' => $id]);
        } catch (Throwable $e) {
            error_log('[SESSION DESTROY ERROR] ' . $e->getMessage());
        }
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->pdo()->prepare('DELETE FROM sessions WHERE last_activity < :cutoff');
            $stmt->execute(['cutoff' => time() - max(1, $max_lifetime)]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[SESSION GC ERROR] ' . $e->getMessage());
            return false;
        }
    }
}
