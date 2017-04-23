<?php

namespace Laravel\Passport;

use Illuminate\Support\Arr;

class PassportSchema
{
    /**
     * The schema configuration.
     *
     * @var array
     */
    public static $config = [
        'user' => [
            'id' => [
                'big' => false,
                'unsigned' => true,
            ],
            'relation' => [
                'table' => 'users',
                'key' => 'id',
                'on_delete' => 'cascade',
            ],
        ],
        'big_integers' => false,
    ];

    /**
     * Get a configuration.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function config($key, $default = null)
    {
        return Arr::get(self::$config, $key, $default);
    }

    /**
     * Add auto-incrementing column.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $column
     *
     * @return \Illuminate\Support\Fluent
     */
    public static function increments($table, $column)
    {
        if (self::config('big_integers')) {
            return $table->bigIncrements($column);
        }

        return $table->increments($column);
    }

    /**
     * Add integer column.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $column
     * @param bool $unsigned
     *
     * @return \Illuminate\Support\Fluent
     */
    public static function integer($table, $column, $unsigned = false)
    {
        if (self::config('big_integers')) {
            return $table->bigInteger($column, false, $unsigned);
        }

        return $table->integer($column, false, $unsigned);
    }

    /**
     * Add user_id column and foreign key.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool $nullable
     *
     * @return \Illuminate\Support\Fluent
     */
    public static function userId($table, $nullable = false)
    {
        $unsigned = static::config('user.id.unsigned');

        if (static::config('user.id.big')) {
            $userId = $table->bigInteger('user_id', false, $unsigned);
        } else {
            $userId = $table->integer('user_id', false, $unsigned);
        }

        $userId->index();

        if ($nullable) {
            $userId->nullable()->default(null);
        }

        if ($userTable = static::config('user.relation.table')) {
            $relation = $table->foreign(['user_id'])
                ->references(static::config('user.relation.key'))
                ->on($userTable);
            if ($onDelete = static::config('user.relation.on_delete')) {
                $relation->onDelete($onDelete);
            }
        }
        return $userId;
    }
}
