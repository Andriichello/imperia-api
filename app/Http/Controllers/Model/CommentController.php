<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Comment\IndexCommentRequest;
use App\Http\Requests\Comment\ShowCommentRequest;
use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Comment\CommentResource;
use App\Repositories\CommentRepository;

/**
 * Class CommentController.
 */
class CommentController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = CommentResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = CommentCollection::class;

    /**
     * CommentRepository constructor.
     *
     * @param CommentRepository $repository
     */
    public function __construct(CommentRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexCommentRequest::class;
        $this->actions['show'] = ShowCommentRequest::class;
    }
}
