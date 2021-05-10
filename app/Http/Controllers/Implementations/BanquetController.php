<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Constrainter;
use App\Http\Controllers\DynamicController;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BanquetController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Banquet::class;

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
            $instance = new $this->model();
            $instance->fill($columns);

            DB::beginTransaction();

            if (!$instance->save()) {
                throw new \Exception('Error while inserting record into the database.');
            }

            $orders = [];
            $orderColumnNames = Banquet::getOrderColumnNames();
            foreach ($columns as $key => $value) {
                if (in_array($key, $orderColumnNames)) {
                    $orders[$key] = $value;
                }
            }

            foreach ($orders as $orderColumnName => $order) {
                $orderController = new OrderController();
                $orderType = array_search($orderColumnName, $orderColumnNames);

                $orderController->switchModel(['type' => $orderType], 'type');
                $order['banquet_id'] = $instance->id;
                $validatedOrder = $this->validateRules(
                    $order,
                    $orderController->getModelValidationRules(true)
                );
                $orderController->createModel($validatedOrder, false);
            }

            $comments = $columns['comments'] ?? [];
            foreach ($comments as $comment) {
                $commentController = new CommentController();
                if (
                    !isset($comment['container_id']) ||
                    !isset($comment['container_type'])
                ) {
                    $comment['container_id'] = $instance->id;
                    $comment['container_type'] = $instance->type;
                }
                $validatedComment = $this->validateRules(
                    $comment,
                    $commentController->getModelValidationRules(true)
                );

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

            if (!$instance->update()) {
                throw new \Exception('Error while updating record in the database.');
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
                $orderController = new OrderController();
                foreach ($newOrders as $orderColumnName => $newOrder) {
                    $orderType = array_search($orderColumnName, $orderColumnNames);

                    $orderController->switchModel(['type' => $orderType], 'type');
                    if (isset($oldOrders[$orderColumnName])) {
                        // updating old order
                        $validatedOrder = $this->validateRules(
                            $newOrder,
                            $orderController->getModelValidationRules(false)
                        );

                        $success = $orderController->updateModel($oldOrders[$orderColumnName], $validatedOrder, false);
                        if (!$success) {
                            return false;
                        }
                    } else {
                        // creating new order
                        $newOrder['banquet_id'] = $instance->id;
                        $validatedOrder = $this->validateRules(
                            $newOrder,
                            $orderController->getModelValidationRules(true)
                        );
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

                $commentController = new CommentController();
                // updating and deleting old comments
                foreach ($instance->comments as $oldComment) {
                    $updateComment = null;
                    foreach ($newComments as $newComment) {
                        if (!isset($newComment->id)) {
                            continue;
                        }

                        if (
                            $oldComment->id == $newComment->id &&
                            $oldComment->target_id == $newComment->target_id &&
                            $oldComment->target_type == $newComment->target_type &&
                            $oldComment->container_id == $newComment->container_id &&
                            $oldComment->container_type == $newComment->container_type
                        ) {
                            $updateComment = $newComment;
                            break;
                        }
                    }

                    if (isset($updateComment)) {
                        // updating old comment
                        $validatedComment = $this->validateRules(
                            $updateComment,
                            $commentController->getModelValidationRules(false)
                        );
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
                        $validatedComment = $this->validateRules(
                            $newComment,
                            $commentController->getModelValidationRules(true)
                        );
                        $commentController->createModel($validatedComment);
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Delete Model instance from the database.
     *
     * @param Model $instance
     * @return bool
     */
    public function destroyModel(Model $instance): bool
    {
        try {
            DB::beginTransaction();

            if (!$instance->update()) {
                throw new \Exception('Error while updating record in the database.');
            }

            $orderController = new OrderController();
            foreach (Banquet::getOrderRelationshipNames() as $orderType => $orderRelationshipName) {
                if (isset($instance->$orderRelationshipName)) {
                    $success = $orderController->destroyModel($instance->$orderRelationshipName, false, $orderType);
                    if (!$success) {
                        return false;
                    }
                }
            }

            $commentController = new CommentController();
            foreach ($instance->comments as $comment) {
                $success = $commentController->destroyModel($comment);
                if (!$success) {
                    return false;
                }
            }

            if (!$instance->delete()) {
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
