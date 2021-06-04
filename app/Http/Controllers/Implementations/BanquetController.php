<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\BanquetStoreRequest;
use App\Http\Requests\BanquetUpdateRequest;
use App\Models\Banquet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BanquetController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var ?string
     */
    protected ?string $model = Banquet::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = BanquetStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = BanquetUpdateRequest::class;

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

            foreach ($orders as $orderColumnName => $order) {
                $orderController = new OrderController();
                $orderType = array_search($orderColumnName, $orderColumnNames);

                $orderController->switchModel($orderType);
                $order['banquet_id'] = $instance->id;

                $orderStoreFormRequest = new ($orderController->storeFormRequest());
                $orderStoreFormRequest->setType($orderType);
                $orderStoreFormRequest->setDataFieldName('');

                $validatedOrder = Validator::validate($order, $orderStoreFormRequest->rules());
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

                $commentStoreFormRequest = new ($commentController->storeFormRequest());
                $commentStoreFormRequest->setDataFieldName('');

                $validatedComment = Validator::validate($comment, $commentStoreFormRequest->rules());
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
                $orderController = new OrderController();
                foreach ($newOrders as $orderColumnName => $newOrder) {
                    $orderType = array_search($orderColumnName, $orderColumnNames);

                    $orderController->switchModel($orderType);
                    if (isset($oldOrders[$orderColumnName])) {
                        // updating old order
                        if (!empty($oldOrders[$orderColumnName])) {
                            $newOrder['id'] = $oldOrders[$orderColumnName]->id;
                        }

                        $newOrder['banquet_id'] = $instance->id;
                        $orderUpdateFormRequest = new ($orderController->updateFormRequest());
                        $orderUpdateFormRequest->setType($orderType);
                        $orderUpdateFormRequest->setDataFieldName('');

                        $validatedOrder = Validator::validate($newOrder, $orderUpdateFormRequest->rules());
                        $success = $orderController->updateModel($oldOrders[$orderColumnName], $validatedOrder, false);
                        if (!$success) {
                            return false;
                        }
                    } else {
                        // creating new order
                        $newOrder['banquet_id'] = $instance->id;
                        $orderStoreFormRequest = new ($orderController->storeFormRequest());
                        $orderStoreFormRequest->setType($orderType);
                        $orderStoreFormRequest->setDataFieldName('');

                        $validatedOrder = Validator::validate($newOrder, $orderStoreFormRequest->rules());
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
                        $commentUpdateFormRequest = new ($commentController->updateFormRequest());
                        $commentUpdateFormRequest->setDataFieldName('');

                        $validatedComment = Validator::validate($updateComment, $commentUpdateFormRequest->rules());
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
                        $commentStoreFormRequest = new ($commentController->storeFormRequest());
                        $commentStoreFormRequest->setDataFieldName('');

                        $validatedComment = Validator::validate($newComment, $commentStoreFormRequest->rules());
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
