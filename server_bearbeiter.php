<?php
// Заголовки для кеширования и CORS, поскольку клиент открывается на другом порту
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 1 Sep 2008 07:30:00 GMT");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Обработка preflight-запроса (что часто встречается в современных браузерах) OPTIONS для CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Получение данных из тела POST-запроса в формате JSON
$data = json_decode(file_get_contents("php://input"), true);
// Извлечение выражения из полученных данных
$expression = isset($data['expression']) ? $data['expression'] : '';

// Инициализация переменной для результата
$result = '';

// Функция для оценки математического выражения
function evaluateExpression($expr) {
    // Приведение выражения к нижнему регистру и удаление лишних пробелов
    $expr = strtolower(trim($expr));
    
    // Проверка, является ли выражение функцией вида func(params)
    if (preg_match('/^(\w+)\s*\(\s*([^\)]+)\s*\)$/', $expr, $matches)) {
        // Распознавание функции и её параметров
        // Получение названия функции
        $func = $matches[1];
        // Разделение параметров по запятой
        $params = explode(',', $matches[2]);
        // Удаление лишних пробелов из параметров
        $params = array_map('trim', $params);
        
        // Обработка различных функций
        switch ($func) {
            case 'sin':
                // Вычисление синуса в градусах
                return sin(deg2rad($params[0]));
            case 'cos':
                return cos(deg2rad($params[0]));
            case 'tan':
                return tan(deg2rad($params[0]));
            case 'cot':
                return 1 / tan(deg2rad($params[0]));
            case 'pow':
                if (count($params) == 2) {
                    // Возведение в степень
                    return pow((float)$params[0], (float)$params[1]);
                }
                break;
            case 'root':
                if (count($params) == 2) {
                    // Получение степени корня
                    $n = (float)$params[0];
                    // Получения числа под корнем
                    $x = (float)$params[1];
                    if ($n != 0) {
                        // Вычисление корня n-й степени
                        return pow($x, 1 / $n);
                    }
                }
                break;
            default:
                // Обработка неизвестной функции
                return "Uncorrectly function";
        }
    } else {
        // Обработка простых арифметических выражений с операциями +, -, *, /
        if (preg_match('/^(-?\d+\.?\d*)\s*([\+\-\*\/])\s*(-?\d+\.?\d*)$/', $expr, $matches2)) {
            // Получение первого операнда
            $a = (float)$matches2[1];
            // Получение второго операнда
            $b = (float)$matches2[3];
            switch ($matches2[2]) {
                case '+': return $a + $b;
                case '-': return $a - $b;
                case '*': return $a * $b;
                // Деление с проверкой деления на ноль
                case '/': return ($b != 0) ? $a / $b : "Divide by 0";
            }
        }
    }
    // Обработка некорректного выражения
    return "Uncorrectly expression";
}

// Обработка входных данных
if (!empty($expression)) {
    // Вызов функции оценки выражения
    $result = evaluateExpression($expression);
    // Возврат результата в формате JSON
    echo json_encode(['result' => $result]);
} else {
    // Если выражение не передано, возвращаем сообщение
    echo json_encode(['result' => 'Enter expression']);
}
?>