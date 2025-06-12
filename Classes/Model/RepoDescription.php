<?php

namespace RKW\OaiConnector\Model;

class RepoDescription
{
    public string $repo;
    public string $description;
    public int $rank;
    public string $updated;
    public string $comment;

    public function __construct(array $data = [])
    {
        $this->repo = $data['repo'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->rank = (int)($data['rank'] ?? 1);
        $this->updated = $data['updated'] ?? date('Y-m-d H:i:s');
        $this->comment = $data['comment'] ?? '';
    }
}
