<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'email',
        'token',
        'expires_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function createToken(string $email, int $expiresInMinutes = 60): string
    {
        $this->where('email', $email)->delete();

        $rawToken = bin2hex(random_bytes(32));

        $this->insert([
            'email'      => $email,
            'token'      => hash('sha256', $rawToken),
            'expires_at' => date('Y-m-d H:i:s', time() + ($expiresInMinutes * 60)),
        ]);

        return $rawToken;
    }

    public function findValidToken(string $rawToken): ?array
    {
        $record = $this->where('token', hash('sha256', $rawToken))->first();

        if ($record === null) {
            return null;
        }

        if (strtotime($record['expires_at']) < time()) {
            $this->delete($record['id']);
            return null;
        }

        return $record;
    }

    public function clearForEmail(string $email): void
    {
        $this->where('email', $email)->delete();
    }
}
