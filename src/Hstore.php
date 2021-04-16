<?php

namespace Scaleplan\Hstore;

class Hstore
{
    /**
     * @param string $sql       - SQL-запрос
     * @param string $fieldName - какое поле использовать
     * @param array  $data      - обрабатываемые данные
     */
    public static function arrayToHstore(string &$sql, string $fieldName, array &$data): void
    {
        if (!array_key_exists($fieldName, $data)) {
            throw new \RuntimeException('Field name not found.');
        }

        if (!array_key_exists(0, $data[$fieldName])) {
            $data[$fieldName] = [$data[$fieldName]];
        }

        if (!is_array(reset($data[$fieldName]))) {
            return;
        }

        $hstoreArray = [];
        foreach ($data[$fieldName] as $index => &$record) {
            foreach ($record as $key => &$value) {
                if (!is_scalar($value)) {
                    throw new \RuntimeException('Hstore value must be scalar.');
                }

                $newKey = $fieldName . $key . $index;
                $hstoreArray[$index][] = "hstore('$key', :$newKey)";
                $returnData[$newKey] = $value[$index];
            }
            unset($value);

            $hstoreArray[$index] = implode(' || ', $hstoreArray[$index]);
        }
        unset($record);

        $sql = str_replace(":$fieldName", 'ARRAY[' . implode(', ', $hstoreArray) . ']::hstore[]', $sql);
    }
}