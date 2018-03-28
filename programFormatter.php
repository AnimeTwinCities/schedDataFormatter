<?php

$jsonData = [];

$row = 0;
$fileCategory = '';

$csvFiles = glob("in*.csv");
foreach ($csvFiles as $fileName) {
    if (($handle = @fopen($fileName, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $category = $data[0] ?? "";
            if ($row == 0) {
                $row++;
                continue;
            }

            if (!$category) {
                continue;
            }
            $fileCategory = $category;

            $start = $data[1] ?? "";
            $end = $data[2] ?? "";
            $room = $data[3] ?? "";
            $name = $data[4] ?? "";
            $description = $data[5] ?? "";
            $hosts = [];
            for ($i = 0; $i < 6; $i++) {
                if (array_key_exists(6 + $i, $data)) {
                    $value = $data[6 + $i];
                    if ($value) {
                        $key = (string)count($hosts);
                        $hosts[$key] = $value;
                    }
                }
            }
            $tags = [];
            if (array_key_exists(12, $data) && $data[12]) {
                $key = (string)count($tags);
                $tags[$key] = 'asl';
            }
            if (array_key_exists(13, $data) && $data[13]) {
                $key = (string)count($tags);
                $tags[$key] = '18+';
            }
            if (array_key_exists(14, $data) && $data[14]) {
                $key = (string)count($tags);
                $tags[$key] = '21+';
            }

            $tmp = [
                'category' => $category,
                'start' => $start,
                'end' => $end,
                'room' => $room,
                'name' => $name,
                'description' => $description,
            ];
            if (count($hosts)) {
                $tmp['hosts'] = $hosts;
            }
            if (count($tags)) {
                $tmp['tags'] = $tags;
            }

            $lowerCat = preg_replace('/[^a-z]/i', '', $category);
            $cleanName = preg_replace('/[^a-z]/i', '', $name);
            $rowName = str_pad($row, 4, "0", STR_PAD_LEFT);

            $jsonKey = "{$lowerCat}_{$rowName}";

            $jsonData[$jsonKey] = $tmp;
            $row++;
        }
        fclose($handle);

    } else {
        echo "in.csv was not found.\r\n";
    }
}

if (count($jsonData)) {
    file_put_contents("compiledList.json", json_encode($jsonData, JSON_FORCE_OBJECT));
}
