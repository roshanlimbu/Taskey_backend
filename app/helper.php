<?php

function res($status, $data = [], $msg = [], $headers = [], $options = 0)
{
    return response()->json(compact('status', 'data', 'msg'), 200, $headers, $options);
}
