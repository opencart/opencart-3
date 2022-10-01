<?php
class ModelToolBackup extends Model {
    public function getTables(): array {
        $table_data = [];

        $query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

        foreach ($query->rows as $result) {
            $table = reset($result);
            if ($table && utf8_substr($table, 0, strlen(DB_PREFIX)) == DB_PREFIX) {
                $table_data[] = $table;
            }
        }

        return $table_data;
    }

    public function backup(array $tables): string {
        $output = '';

        foreach ($tables as $table) {
            if (DB_PREFIX) {
                if (strpos($table, DB_PREFIX) === false) {
                    $status = false;
                } else {
                    $status = true;
                }
            } else {
                $status = true;
            }

            if ($status) {
                $output .= 'TRUNCATE TABLE `' . $table . '`;' . "\n\n";

                $query = $this->db->query("SELECT * FROM `" . $table . "`");

                foreach ($query->rows as $result) {
                    $fields = '';

                    foreach (array_keys($result) as $value) {
                        $fields .= '`' . $value . '`, ';
                    }

                    $values = '';

                    foreach (array_values($result) as $value) {
                        $value = str_replace(["\x00", "\x0a", "\x0d", "\x1a"], ['\0', '\n', '\r', '\Z'], $value);
                        $value = str_replace(["\n", "\r", "\t"], ['\n', '\r', '\t'], $value);
                        $value = str_replace('\\', '\\\\', $value);
                        $value = str_replace('\'', '\\\'', $value);
                        $value = str_replace('\\\n', '\n', $value);
                        $value = str_replace('\\\r', '\r', $value);
                        $value = str_replace('\\\t', '\t', $value);

                        $values .= '\'' . $value . '\', ';
                    }

                    $output .= 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
                }

                $output .= "\n\n";
            }
        }

        return $output;
    }
}