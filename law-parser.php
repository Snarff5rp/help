<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

$lawUrl = 'https://forum.majestic-rp.ru/threads/ugolovnyi-kodeks-shtata-san-andreas.2247404/';

function logError($message) {
    file_put_contents('parser-errors.log', date('[Y-m-d H:i:s]') . " " . $message . "\n", FILE_APPEND);
}

try {
    // Получение HTML
    $html = file_get_contents($lawUrl);
    if (!$html) throw new Exception("Failed to fetch URL");
    
    // Парсинг DOM
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $articles = [];
    
    // Поиск всех статей (адаптируйте селектор!)
    $sections = $xpath->query("//div[contains(@class, 'message-content')]//article");
    
    foreach ($sections as $section) {
        try {
            // Извлечение заголовка
            $titleNode = $xpath->query(".//h2", $section)->item(0);
            $title = $titleNode ? trim($titleNode->nodeValue) : 'Без названия';
            
            // Извлечение номера статьи
            preg_match('/Статья\s+(\d+[а-яА-Я]?)/u', $title, $matches);
            $articleNum = $matches[1] ?? '';
            
            // Извлечение содержания
            $contentNode = $xpath->query(".//div[contains(@class, 'bbWrapper')]", $section)->item(0);
            $content = $contentNode ? trim($contentNode->nodeValue) : '';
            
            // Очистка и форматирование
            $content = preg_replace('/\s+/u', ' ', $content); // Удаление лишних пробелов
            
            $articles[] = [
                'article' => $articleNum ? "Статья $articleNum" : "Без номера",
                'title' => $title,
                'content' => $content,
                'tags' => generateTags($title, $content)
            ];
            
        } catch (Exception $e) {
            logError("Ошибка обработки статьи: " . $e->getMessage());
        }
    }
    
    // Кэширование
    $cacheData = json_encode(['success' => true, 'articles' => $articles]);
    echo $cacheData;
    
} catch (Exception $e) {
    logError($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка парсинга',
        'details' => $e->getMessage()
    ]);
}

function generateTags($title, $content) {
    $text = mb_strtolower($title . ' ' . $content);
    $tags = [];
    
    // Ключевые слова для тегов
    $keywords = [
        'убийство', 'грабеж', 'разбой', 'наркотик', 
        'повреждение', 'хулиганство', 'вымогательство',
        'штраф', 'арест', 'лишение', 'срок', 'тюрьма'
    ];
    
    foreach ($keywords as $keyword) {
        if (mb_strpos($text, $keyword) !== false) {
            $tags[] = $keyword;
        }
    }
    
    // Автоматическое выделение ключевых слов (дополнительно)
    $words = array_count_values(str_word_count($text, 1));
    arsort($words);
    $frequent = array_slice(array_keys($words), 0, 5);
    
    return array_unique(array_merge($tags, $frequent));
}
?>
