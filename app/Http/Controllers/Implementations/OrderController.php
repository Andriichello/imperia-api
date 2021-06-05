<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicTypedController;
use App\Http\Requests\Implementations\OrderRequest;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderController extends DynamicTypedController
{
    /**
     * Controller's model class name.
     *
     * @var ?string
     */
    protected ?string $model = Order::class;

    public function getTypeModels(): array
    {
        return Order::getTypeModels();
    }

    public function __construct(OrderRequest $request, ?string $type = null)
    {
        parent::__construct($request, $type);
    }

    /**
     * Create new Model instance and store it in the database.
     *
     * @param array $columns
     * @param bool $beginTransaction
     * @param string|null $type
     * @return Model
     * @throws \Exception
     */
    public function createModel(array $columns, bool $beginTransaction = true, string $type = null): Model
    {
        if (isset($type)) {
            $this->switchModel($type);
        }

        try {
            $instance = new $this->model();
            $instance->fill($columns);
            $instance->setAppends([]);

            if ($beginTransaction) {
                DB::beginTransaction();
            }

            if (!$instance->save()) {
                throw new \Exception('Error while inserting record into the database.', 520);
            }

            $newFields = $this->toFields($instance, $columns['items'] ?? []);
            foreach ($newFields as $newField) {
                if (!$newField->save()) {
                    throw new \Exception('Error while inserting field record into the database.', 520);
                }
            }

            if ($beginTransaction) {
                DB::commit();
            }

            $instance->setAppends(['items', 'comments']);
            return $instance->refresh();
        } catch (\Exception $exception) {
            if ($beginTransaction) {
                DB::rollBack();
            }
            throw $exception;
        }
    }

    /**
     * Update Model instance in the database.
     *
     * @param Model $instance
     * @param array $columns
     * @param bool $beginTransaction
     * @param string|null $type
     * @return bool
     * @throws \Exception
     */
    public function updateModel(Model $instance, array $columns = [], bool $beginTransaction = true, string $type = null): bool
    {
        if (isset($type)) {
            $this->switchModel($type);
        }

        try {
            if ($beginTransaction) {
                DB::beginTransaction();
            }

            if (!$instance->update($columns)) {
                return false;
            }

            if (array_key_exists('items', $columns)) {
                $newItems = $columns['items'] ?? [];

                // there were no items but now there is
                if (empty($instance->items) && !empty($newItems)) {
                    $newFields = $this->toFields($instance, $newItems);
                    foreach ($newFields as $newField) {
                        if (!$newField->save()) {
                            return false;
                        }
                    }
                } // there was at least one item but now there isn't
                else if (empty($newItems) && !empty($instance->items)) {
                    $itemIdKey = $this->getType() . '_id';
                    foreach ($instance->fields as $oldField) {
                        $fieldsTableName = Order::getTypeFieldTableName($this->getType());
                        $success = DB::table($fieldsTableName)
                            ->where('order_id', $oldField->order_id)
                            ->where($itemIdKey, $oldField->$itemIdKey)
                            ->delete();

                        if (!$success) {
                            return false;
                        }
                    }
                } // there was at least one item and now there is at least one
                else {
                    $newFields = $this->toFields($instance, $newItems);

                    $itemIdKey = $this->getType() . '_id';
                    foreach ($instance->fields as $oldField) {
                        $isThere = false;
                        foreach ($newFields as $newField) {
                            if ($oldField->$itemIdKey == $newField->$itemIdKey) {
                                $isThere = true;
                            }
                        }

                        if (!$isThere) {
                            $fieldsTableName = Order::getTypeFieldTableName($this->getType());
                            $success = DB::table($fieldsTableName)
                                ->where('order_id', $oldField->order_id)
                                ->where($itemIdKey, $oldField->$itemIdKey)
                                ->delete();

                            if (!$success) {
                                return false;
                            }
                        }
                    }

                    foreach ($newFields as $newField) {
                        $isThere = false;
                        foreach ($instance->fields as $oldField) {
                            if ($oldField->$itemIdKey == $newField->$itemIdKey) {
                                $oldField->fill($newField->attributesToArray());
                                $updates = [];
                                foreach ($oldField->getFillable() as $attrributeName) {
                                    if ($oldField->isDirty($attrributeName)) {
                                        $updates[$attrributeName] = $oldField->$attrributeName;
                                    }
                                }
                                if (!empty($updates)) {
                                    $fieldsTableName = Order::getTypeFieldTableName($this->getType());
                                    $success = DB::table($fieldsTableName)
                                        ->where('order_id', $oldField->order_id)
                                        ->where($itemIdKey, $oldField->$itemIdKey)
                                        ->update($updates);

                                    if (!$success) {
                                        return false;
                                    }
                                }

                                $isThere = true;
                            }
                        }

                        if (!$isThere) {
                            if (!$newField->save()) {
                                return false;
                            }
                        }
                    }
                }
            }

            if ($beginTransaction) {
                DB::commit();
            }
            $instance->refresh();
            return true;
        } catch (\Exception $exception) {
            if ($beginTransaction) {
                DB::rollBack();
            }
            throw $exception;
        }
    }


    /**
     * Delete new model instance from the database.
     *
     * @param Model $instance
     * @param bool $softDelete
     * @param string|null $type
     * @throws \Exception
     */
    public function destroyModel(Model $instance, bool $softDelete = true, ?string $type = null): bool
    {
        if (isset($type)) {
            $this->switchModel($type);
        }

        return parent::destroyModel($instance, false);
    }

    /**
     * Convert items to Fields
     *
     * @param Model $order
     * @param array $items
     * @return array
     */
    public function toFields(Model $order, array $items): array
    {
        $fields = [];

        $itemIdKey = $this->getType() . '_id';
        $orderId = data_get($order, 'id');
        foreach ($items as $item) {
            $itemId = data_get($item, 'id');
            if (isset($itemId) && isset($orderId)) {
                $field = new (Order::getTypeFields()[$this->getType()]);
                $field->fill($item);
                $field->order_id = $orderId;
                $field->$itemIdKey = $itemId;

                $fields[] = $field;
            }
        }
        return $fields;
    }
}
