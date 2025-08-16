<?php
include 'config.php';


$type = filter_input(INPUT_POST, 'type', FILTER_DEFAULT);
$mode = filter_input(INPUT_POST, 'mode', FILTER_DEFAULT);
$current_page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT) ?? 1;
$user_answers = $_POST['answers'] ?? [];

// 严格验证参数
$valid_types = ['single', 'multiple', 'truefalse', 'fill'];
$valid_modes = ['random', 'sequential', 'review'];

if (!in_array($type, $valid_types)) {
    die('<div class="alert alert-danger">无效的题目类型</div>');
}

if (!in_array($mode, $valid_modes)) {
    die('<div class="alert alert-danger">无效的练习模式</div>');
}

// 数据库表映射
$table_mapping = [
    'single' => 'single_choice',
    'multiple' => 'multiple_choice',
    'truefalse' => 'true_false',
    'fill' => 'fill_in_the_blanks'
];
$table = $table_mapping[$type] ?? 'single_choice';

// 处理顺序模式单题提交
if ($mode === 'sequential') {
    handleSequentialMode($pdo, $table, $type, $user_answers, $current_page);
    exit;
}

// 处理其他模式提交
handleNormalMode($pdo, $table, $type, $user_answers);

/**
 * 处理顺序模式提交
 */
function handleSequentialMode($pdo, $table, $type, $user_answers, $current_page) {
    if (empty($user_answers)) {
        showError('未提交任何答案');
    }
    // 新增总题数查询
    $total = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $max_page = ceil($total / 1);
    $next_page = min($current_page + 1, $max_page);
    
    $question_id = array_key_first($user_answers);
    $user_answer = $user_answers[$question_id];

    // 获取正确答案
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$question_id]);
    $question = $stmt->fetch();

    if (!$question) {
        showError('题目不存在');
    }

    // 验证答案
    $result = checkAnswer($type, $question['answer'], $user_answer);

    // 显示结果
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>答题结果</title>
        <link href="status/bootstrap.min.css" rel="stylesheet">
        <style>
            .difficulty-badge { position: absolute; right: 1rem; top: 1rem; }
            .answer-card { border-left: 4px solid #0d6efd; }
        </style>
    </head>
    <body class="bg-light">
        <div class="container py-4">
            <div class="card answer-card shadow-sm mb-4">
                <div class="card-body position-relative">
                    
                    
                    <h5 class="card-title mb-3">'.$question['question'].'</h5>
                    
                    <div class="alert '.($result['correct'] ? 'alert-success' : 'alert-danger').'">
                        <h5><i class="fas fa-check-circle"></i> '.($result['correct'] ? '回答正确' : '回答错误').'</h5>
                        <p class="mb-1">你的答案：'.formatAnswer($type, $result['user_answer']).'</p>
                        <p class="mb-0">正确答案：'.formatAnswer($type, $result['correct_answer']).'</p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="practice.php?type='.$type.'&mode=sequential&page='.$next_page.'" 
                           class="btn btn-primary btn-lg">
                           <i class="fas fa-arrow-right me-2"></i>继续下一题
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * 处理常规模式提交
 */
function handleNormalMode($pdo, $table, $type, $user_answers) {
    $question_ids = array_keys($user_answers);
    
    if (empty($question_ids)) {
        showError('未提交任何答案');
    }

    // 获取正确答案
    $in = str_repeat('?,', count($question_ids)-1).'?';
    $stmt = $pdo->prepare("SELECT id, question, answer, difficulty FROM $table WHERE id IN ($in)");
    $stmt->execute($question_ids);
    $correct_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 处理结果
    $results = [];
    foreach ($user_answers as $qid => $user_answer) {
        $correct_data = findCorrectAnswer($correct_answers, $qid);
        if (!$correct_data) continue;

        $result = checkAnswer($type, $correct_data['answer'], $user_answer);
        $results[] = [
            'question' => $correct_data['question'],
            'difficulty' => $correct_data['difficulty'],
            'correct_answer' => $correct_data['answer'],
            'user_answer' => $user_answer,
            'correct' => $result['correct']
        ];
    }

    // 显示结果
    showResults($type, $results);
}

/**
 * 检查答案正确性
 */
function checkAnswer($type, $correct_answer, $user_answer) {
    $correct = false;
    $normalized_correct = trim(strtolower($correct_answer));
    $normalized_user = is_array($user_answer) 
        ? array_map('trim', $user_answer)
        : trim($user_answer);

    switch ($type) {
        case 'multiple':
            // 处理正确答案（支持 ABCD 和 A,B,C,D 两种格式）
            $correct_parts = [];
            if (strpos($normalized_correct, ',') !== false) {
                $correct_parts = explode(',', $normalized_correct);
            } else {
                $correct_parts = str_split($normalized_correct);
            }
            
            // 清洗数据：去空格、转大写、排序
            $correct_parts = array_map(function($v) {
                return strtoupper(trim($v));
            }, $correct_parts);
            sort($correct_parts);
            
            // 处理用户答案
            $user_parts = is_array($normalized_user) 
                ? $normalized_user 
                : explode(',', $normalized_user);
            
            $user_parts = array_map(function($v) {
                return strtoupper(trim($v));
            }, $user_parts);
            sort($user_parts);
            
            $correct = ($correct_parts == $user_parts);
            break;

        case 'single':
        case 'truefalse':
            $correct = (strtoupper(trim($normalized_user)) === strtoupper(trim($normalized_correct)));
            break;

        case 'fill':
            $correct = (trim($normalized_user) === trim($normalized_correct));
            break;
    }

    return [
        'correct' => $correct,
        'user_answer' => is_array($user_answer) ? implode(', ', $user_answer) : $user_answer,
        'correct_answer' => $correct_answer
    ];
}

/**
 * 显示完整结果
 */
function showResults($type, $results) {
    $total = count($results);
    $correct_count = count(array_filter($results, fn($r) => $r['correct']));
    $score = round(($correct_count / $total) * 100, 1);

    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>答题结果</title>
        <link href="status/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            .result-card { border-left: 4px solid; }
            .correct { border-color: #198754; }
            .wrong { border-color: #dc3545; }
            .difficulty-badge { position: absolute; right: 1rem; top: 1rem; }
        </style>
    </head>
    <body class="bg-light">
        <div class="container py-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h2 class="card-title mb-3">
                        <i class="fas fa-poll me-2"></i>答题结果
                    </h2>
                    <div class="display-4 fw-bold '.($score >= 60 ? 'text-success' : 'text-danger').'">
                        '.$score.'分
                    </div>
                    <div class="text-muted">
                        正确 '.$correct_count.' 题 / 共 '.$total.' 题
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>返回
            </a>
                </div>
            </div>';

    foreach ($results as $result) {
        echo '<div class="card result-card shadow-sm mb-3 '.($result['correct'] ? 'correct' : 'wrong').'">
                <div class="card-body position-relative">
                    
                    
                    <h5 class="card-title">'.$result['question'].'</h5>
                    
                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <span class="badge bg-success text-white p-2 w-100">
                                <i class="fas fa-check-circle me-1"></i>
                                正确：'.formatAnswer($type, $result['correct_answer']).'
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="badge '.($result['correct'] ? 'bg-success' : 'bg-danger').' text-white p-2 w-100">
                                <i class="fas fa-user-circle me-1"></i>
                                你的：'.formatAnswer($type, $result['user_answer']).'
                            </span>
                        </div>
                    </div>
                </div>
            </div>';
    }

    echo '</div></body></html>';
}

/**
 * 辅助函数
 */
function getDifficultyColor($difficulty) {
    return match(strtolower($difficulty)) {
        'easy' => 'success',
        'medium' => 'warning',
        'hard' => 'danger',
        default => 'secondary'
    };
}

function formatAnswer($type, $answer) {
    if (is_array($answer)) {
        return implode(', ', $answer);
    }
    return htmlspecialchars($answer);
}

function findCorrectAnswer($answers, $qid) {
    foreach ($answers as $answer) {
        if ($answer['id'] == $qid) {
            return $answer;
        }
    }
    return null;
}

function showError($message) {
    echo '<div class="alert alert-danger">'.$message.'</div>';
    exit;
}
?>
