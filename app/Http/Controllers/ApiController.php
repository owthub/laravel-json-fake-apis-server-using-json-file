<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use plejus\PhpPluralize\Inflector;

class ApiController extends Controller
{
    private $path;
    private $json;

    public function __construct()
    {
        $this->path = storage_path() . "/json/db.json";
        $this->json = json_decode(file_get_contents($this->path), true);
    }

    public function index()
    {
        $jsonKeysArr = array_keys($this->json);
        return view('welcome', array(
            "routes" => $jsonKeysArr
        ));
    }

    public function loadJson()
    {
        return response()->json($this->json);
    }

    public function specificJSONKey($key)
    {
        $jsonKeysArr = array_keys($this->json);

        $jsonKey = $key;

        $queryParamArr = request()->query();

        if (!empty($queryParamArr)) {

            $paramsArray = ["id"];

            if (count($jsonKeysArr) > 0) {
                $paramsArray = array_merge($paramsArray, array_map(function ($plural) {
                    $inflector = new Inflector();
                    return $inflector->singular($plural) . "Id";
                }, $jsonKeysArr));
            }

            if (in_array(key($queryParamArr), $paramsArray)) {

                if (strtolower(key($queryParamArr)) == "id") {

                    $dataId = intval($queryParamArr[strtolower(key($queryParamArr))]);

                    if (in_array($jsonKey, $jsonKeysArr)) {

                        $keyDataArr = $this->json[$jsonKey];

                        if (array_key_exists(($dataId - 1), $keyDataArr)) {
                            return $this->dataFoundResponse($keyDataArr[$dataId - 1]);
                        } else {
                            return $this->noDataFoundResponse();
                        }
                    } else {
                        return $this->noDataFoundResponse();
                    }
                } else {

                    $inflector = new Inflector();
                    $searchValue = intval($queryParamArr[key($queryParamArr)]);
                    $by = key($queryParamArr);
                    $from = $jsonKey;
                    if (in_array($jsonKey, $jsonKeysArr)) {

                        $fromDataArr = $this->json[$from];
                        $fromKeys = array_keys(array_combine(array_keys($fromDataArr), array_column($fromDataArr, $by)), $searchValue);

                        if (count($fromKeys) > 0) {
                            $byValues = [];
                            foreach ($fromKeys as $fromKey) {
                                array_push($byValues, $fromDataArr[$fromKey]);
                            }
                            return $this->dataFoundResponse($byValues);
                        } else {
                            return $this->noDataFoundResponse();
                        }
                    } else {
                        return $this->noDataFoundResponse();
                    }
                }
            } else {
                return $this->noDataFoundResponse();
            }
        } else {
            if (in_array($jsonKey, $jsonKeysArr)) {
                return $this->dataFoundResponse($this->json[$jsonKey]);
            } else {
                return $this->noDataFoundResponse();
            }
        }
    }

    public function specificJSONKeyDataByID($key, $id)
    {
        $jsonKey = $key;
        $dataId = $id;

        $jsonKeysArr = array_keys($this->json);

        if (in_array($jsonKey, $jsonKeysArr)) {

            $keyDataArr = $this->json[$jsonKey];

            if (array_key_exists(($dataId - 1), $keyDataArr)) {
                return $this->dataFoundResponse($keyDataArr[$dataId - 1]);
            } else {
                return $this->noDataFoundResponse();
            }
        } else {
            return $this->noDataFoundResponse();
        }
    }

    public function specificJSONKeyDataFromRelationship($by, $searchValue, $from)
    {
        $jsonKeysArr = array_keys($this->json);

        $inflector = new Inflector();

        if (in_array($by, $jsonKeysArr) && in_array($from, $jsonKeysArr)) {

            $singularBy = $inflector->singular($by);
            $singularByIDKey = $singularBy . "Id";

            $fromDataArr = $this->json[$from];

            $fromKeys = array_keys(array_combine(array_keys($fromDataArr), array_column($fromDataArr, $singularByIDKey)), $searchValue);

            if (count($fromKeys) > 0) {
                $byValues = [];
                foreach ($fromKeys as $fromKey) {
                    array_push($byValues, $fromDataArr[$fromKey]);
                }
                return $this->dataFoundResponse($byValues);
            } else {
                return $this->noDataFoundResponse();
            }
        } else {
            return $this->noDataFoundResponse();
        }
    }

    public function postSpecificJSONKey($key, Request $req)
    {
        $jsonKeysArr = array_keys($this->json);

        $jsonKey = $key;

        if (in_array($jsonKey, $jsonKeysArr)) {
            $existingKeys = array_keys($this->json[$jsonKey][0]);
            $inputs = $req->all();
            if (count($inputs) > 0) {
                unset($existingKeys[0]);
                $dataKeys = array_keys($inputs);
                foreach ($existingKeys as $extKey) {
                    if (!in_array($extKey, $dataKeys) && empty($req->get($extKey))) {
                        return response()->json(["error" => 1, "msg" => "All fields are required", "field" => "{$extKey} missing"]);
                    }
                }
                $outputJson = array_merge($inputs, array(
                    "id" => 101
                ));
                return $this->dataFoundResponse($outputJson, "data created");
            } else {
                return response()->json([
                    "error" => 1,
                    "msg" => "All fields are required",
                    "data" => []
                ]);
            }
        } else {
            return $this->noDataFoundResponse();
        }
    }

    public function putSpecificJSONKeyDataByID($key, $id, Request $req)
    {
        $jsonKeysArr = array_keys($this->json);

        $jsonKey = $key;

        if (in_array($jsonKey, $jsonKeysArr)) {
            if (!empty($this->json[$jsonKey][$id - 1]) && isset($this->json[$jsonKey][$id - 1])) {
                $existingValues = $this->json[$jsonKey][$id - 1];
                $existingKeys = array_keys($this->json[$jsonKey][$id - 1]);
                $inputs = $req->all();

                if (count($inputs) > 0) {
                    $dataKeys = array_keys($inputs);

                    foreach ($existingKeys as $extKey) {
                        if (in_array($extKey, $dataKeys) && !empty($req->get($extKey))) {
                            $existingValues[$extKey] = $req->get($extKey);
                        }
                    }
                    $outputJson = array_merge($existingValues, array(
                        "id" => $id
                    ));
                    return $this->dataFoundResponse($outputJson, "data updated");
                } else {
                    return response()->json([
                        "error" => 1,
                        "msg" => "All fields are required",
                        "data" => []
                    ]);
                }
            } else {
                return $this->noDataFoundResponse();
            }
        } else {
            return $this->noDataFoundResponse();
        }
    }

    public function deleteSpecificJSONKeyDataByID($key, $id, Request $req)
    {
        $jsonKeysArr = array_keys($this->json);

        $jsonKey = $key;

        if (in_array($jsonKey, $jsonKeysArr)) {
            if (!empty($this->json[$jsonKey][$id - 1]) && isset($this->json[$jsonKey][$id - 1])) {
                return response()->json([
                    "error" => 0,
                    "msg" => "data deleted successfully",
                    "data" => []
                ]);
            } else {
                return $this->noDataFoundResponse();
            }
        } else {
            return $this->noDataFoundResponse();
        }
    }

    public function noDataFoundResponse()
    {
        return response()->json([
            "error" => 1,
            "msg" => "no data found",
            "data" => []
        ], 404);
    }

    public function dataFoundResponse($data, $msg = "")
    {
        return response()->json([
            "error" => 0,
            "msg" => !empty($msg) ? $msg : "data found",
            "data" => $data
        ], 200);
    }
}
