<?php

namespace Src\Blog\Comment\Application\Queries;

use PDO;
use Src\Blog\Comment\Application\DTOs\CommentEditDto;

final readonly class FindCommentByIdQuery
{
    public function __construct(
        private PDO $db,
    ) {}

    /** @return CommentEditDto */
    public function handle(int $id): CommentEditDto
    {
        // 暫時合併 Query 和 Handler
        $query = $this;

        $stmt = $this->db->prepare('
            SELECT c.id, c.message
            FROM comments c
            WHERE c.id = :id
        ');

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new CommentEditDto(
            id: $row['id'],
            message: $row['message'],
        );
    }
}
