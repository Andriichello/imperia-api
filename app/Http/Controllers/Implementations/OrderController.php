<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = Order::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = OrderStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = OrderUpdateRequest::class;

    /**
     * Current type.
     *
     * @var string
     */
    protected $currentType;

    public function index($type = null)
    {
        $this->switchModel($type);
        return parent::index();
    }

    public function show($type = null, $id = null)
    {
        $this->switchModel($type);
        return parent::show($id);
    }

    public function store($type = null)
    {
        $this->switchModel($type);
        return parent::store();
    }

    public function update($type = null, $id = null)
    {
        $this->switchModel($type);
        return parent::update($id);
    }

    public function destroy($type = null, $id = null)
    {
        $this->switchModel($type);
        return parent::destroy($id);
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
                    $itemIdKey = $this->currentType . '_id';
                    foreach ($instance->fields as $oldField) {
                        $fieldsTableName = Order::getTypedOrderFieldTableName($this->currentType);
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

                    $itemIdKey = $this->currentType . '_id';
                    foreach ($instance->fields as $oldField) {
                        $isThere = false;
                        foreach ($newFields as $newField) {
                            if ($oldField->$itemIdKey == $newField->$itemIdKey) {
                                $isThere = true;
                            }
                        }

                        if (!$isThere) {
                            $fieldsTableName = Order::getTypedOrderFieldTableName($this->currentType);
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
                                    $fieldsTableName = Order::getTypedOrderFieldTableName($this->currentType);
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

        $itemIdKey = $this->currentType . '_id';
        $orderId = data_get($order, 'id');
        foreach ($items as $item) {
            $itemId = data_get($item, 'id');
            if (isset($itemId) && isset($orderId)) {
                $field = new (Order::getTypeOrderFieldClass($this->currentType));
                $field->fill($item);
                $field->order_id = $orderId;
                $field->$itemIdKey = $itemId;

                $fields[] = $field;
            }
        }
        return $fields;
    }

    /**
     * Switches controller's model class name depending on specified type.
     *
     * @param string|null $dataKey
     * @param array|null $data
     * @return void
     *
     * @throws \Exception
     */
    public function switchModel($data = null, $dataKey = null)
    {
        if (empty($dataKey)) {
            if (is_array($data)) {
                $type = $data[array_key_first($data)];
            } else {
                $type = $data;
            }
        } else {
            $type = data_get($data, $dataKey);
        }

        if (!in_array($type, Order::getTypes())) {
            throw ValidationException::withMessages([
                'type' => ['A type attribute must be one of (' . implode(', ', Order::getTypes()) . ').'],
            ]);
        }

        $this->model = Order::getTypeOrderClass($this->currentType = $type);
    }
}
