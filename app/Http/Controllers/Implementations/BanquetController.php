<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\Implementations\BanquetRequest;
use App\Http\Requests\Implementations\CommentRequest;
use App\Http\Requests\Implementations\OrderRequest;
use App\Models\Banquet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BanquetController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var ?string
     */
    protected ?string $model = Banquet::class;

    public function __construct(BanquetRequest $request)
    {
        parent::__construct($request);
    }

    /**
     * Create new Model instance and store it in the database.
     *
     * @param array $columns
     * @return Model
     * @throws \Exception
     */
    public function createModel(array $columns): Model
    {
        try {
            DB::beginTransaction();
            $instance = parent::createModel($columns);

            if (!isset($instance)) {
                throw new \Exception('Error while inserting record into the database.', 520);
            }

            $orders = [];
            $orderColumnNames = Banquet::getOrderColumnNames();
            foreach ($columns as $key => $value) {
                if (in_array($key, $orderColumnNames)) {
                    $orders[$key] = $value;
                }
            }

            $orderController = new OrderController(new OrderRequest());
            foreach ($orders as $orderColumnName => $order) {
                $orderType = array_search($orderColumnName, $orderColumnNames);

                $orderController->switchModel($orderType);
                $order['banquet_id'] = $instance->id;

                Log::debug('order valiation rules: ' . json_encode($orderController->request()->storeRules(false)));
                $validatedOrder = Validator::validate($order, $orderController->request()->storeRules(false));
                $orderController->createModel($validatedOrder, false);
            }

            $commentController = new CommentController(new CommentRequest());
            $comments = $columns['comments'] ?? [];
            foreach ($comments as $comment) {
                if (
                    !isset($comment['container_id']) ||
                    !isset($comment['container_type'])
                ) {
                    $comment['container_id'] = $instance->id;
                    $comment['container_type'] = $instance->type;
                }
                $validatedComment = Validator::validate($comment, $commentController->request()->storeRules(false));
                $commentController->createModel($validatedComment);
            }

            DB::commit();

            return $instance->refresh();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Update Model instance in the database.
     *
     * @param Model $instance
     * @param array $columns
     * @return bool
     * @throws \Exception
     */
    public function updateModel(Model $instance, array $columns = []): bool
    {
        try {
            DB::beginTransaction();

            if (!$instance->update($columns)) {
                throw new \Exception('Error while updating record in the database.', 520);
            }

            $orderColumnNames = Banquet::getOrderColumnNames();
            $orderRelationshipNames = Banquet::getOrderRelationshipNames();

            $newOrders = [];
            foreach ($orderColumnNames as $orderType => $orderColumnName) {
                if (isset($columns[$orderColumnName])) {
                    $newOrders[$orderColumnName] = $columns[$orderColumnName];
                }
            }

            $oldOrders = [];
            foreach ($orderRelationshipNames as $orderType => $orderRelationshipName) {
                if (isset($instance->$orderRelationshipName)) {
                    $oldOrders[$orderColumnNames[$orderType]] = $instance->$orderRelationshipName;
                }
            }

            if (isset($newOrders)) {
                $orderController = new OrderController(new OrderRequest());
                foreach ($newOrders as $orderColumnName => $newOrder) {
                    $orderType = array_search($orderColumnName, $orderColumnNames);

                    $orderController->switchModel($orderType);
                    if (isset($oldOrders[$orderColumnName])) {
                        // updating old order
                        if (!empty($oldOrders[$orderColumnName])) {
                            $newOrder['id'] = $oldOrders[$orderColumnName]->id;
                        }

                        $newOrder['banquet_id'] = $instance->id;

                        $validatedOrder = Validator::validate($newOrder, $orderController->request()->updateRules(false));
                        $success = $orderController->updateModel($oldOrders[$orderColumnName], $validatedOrder, false);
                        if (!$success) {
                            return false;
                        }
                    } else {
                        // creating new order
                        $newOrder['banquet_id'] = $instance->id;

                        $validatedOrder = Validator::validate($newOrder, $orderController->request()->storeRules(false));
                        $orderController->createModel($validatedOrder, false);
                    }
                }
            }

            if (isset($columns['comments'])) {
                $newComments = $columns['comments'] ?? [];
                foreach ($newComments as $key => $newComment) {
                    if (
                        !isset($newComment['container_id']) ||
                        !isset($newComment['container_type'])
                    ) {
                        $newComments[$key]['container_id'] = $instance->id;
                        $newComments[$key]['container_type'] = $instance->type;
                    }
                }

                $commentController = new CommentController(new CommentRequest());
                // updating and deleting old comments
                foreach ($instance->comments as $oldComment) {
                    $updateComment = null;
                    foreach ($newComments as $newComment) {
                        if (empty($newComment['id'])) {
                            continue;
                        }

                        if (
                            $oldComment->id == $newComment['id'] &&
                            $oldComment->target_id == $newComment['target_id'] &&
                            $oldComment->target_type == $newComment['target_type'] &&
                            $oldComment->container_id == $newComment['container_id'] &&
                            $oldComment->container_type == $newComment['container_type']
                        ) {
                            $updateComment = $newComment;
                            break;
                        }
                    }

                    if (isset($updateComment)) {
                        // updating old comment
                        $validatedComment = Validator::validate($updateComment, $commentController->request()->updateRules(false));
                        if (!$commentController->updateModel($oldComment, $validatedComment)) {
                            return false;
                        }
                    } else {
                        // deleting old comment
                        if (!$commentController->destroyModel($oldComment)) {
                            return false;
                        }
                    }
                }

                // creating new comments
                foreach ($newComments as $newComment) {
                    if (!isset($newComment['id'])) {
                        // creating new comment
                        $validatedComment = Validator::validate($newComment, $commentController->request()->storeRules(false));
                        $commentController->createModel($validatedComment);
                    }
                }
            }

            DB::commit();

            $instance->refresh();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
