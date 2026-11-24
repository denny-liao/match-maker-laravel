<?php

namespace Src\Blog\Comment\Application\Queries;

use Carbon\Carbon;
use PDO;
use Src\Blog\Comment\Application\DTOs\CommentItemDto;
use Src\Blog\Comment\Domain\Enums\CommentStatus;

final readonly class ListCommentsByQuery
{
    public function __construct(
        private PDO $db,
        public int $page = 1,
        public int $perPage = 30,
    ) {}

     /** @return CommentItemDto[] */
    public function handle(/*ListCommentsByQuery $query*/): array
    {
        // 暫時合併 Query 和 Handler
        $query = $this;

        $offset = ($query->page - 1) * $query->perPage;

        // 直接跨表 JOIN 查詢，不載入任何聚合根，效能極高
        $stmt = $this->db->prepare('
            SELECT c.id, c.message, c.created_at, c.author_id, c.author_name, c.donate_amount
            FROM comments c
            WHERE c.status = :status
            ORDER BY c.created_at DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->execute([
            'limit' => $query->perPage,
            'offset' => $offset,
            'status' => CommentStatus::Published->value
        ]);

        return array_map(
            fn(array $row) => new CommentItemDto(
                id: $row['id'],
                message: $row['message'],
                authorId: $row['author_id'],
                authorName: $row['author_name'] ?? '未知使用者',
                amount: $row['donate_amount'],
                createdAt: Carbon::parse($row['created_at'])->diffForHumans(),
            ),
            $stmt->fetchAll(PDO::FETCH_ASSOC) // key => value 形式的陣列
        );
    }
}