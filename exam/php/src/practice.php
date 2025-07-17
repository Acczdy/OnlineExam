<?php
include 'config.php';

// 验证参数
$valid_types = ['single', 'multiple', 'truefalse', 'fill'];
$type = in_array($_GET['type'] ?? '', $valid_types) ? $_GET['type'] : 'single';
$mode = in_array($_GET['mode'] ?? '', ['random', 'sequential', 'review']) ? $_GET['mode'] : 'random';

// 分页处理
$current_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => [
        'default' => 1,
        'min_range' => 1
    ]
]);
$per_page = 1;

// 表名映射
$table_mapping = [
    'single' => 'single_choice',
    'multiple' => 'multiple_choice',
    'truefalse' => 'true_false',
    'fill' => 'fill_in_the_blanks'
];
$table = $table_mapping[$type] ?? 'single_choice';

try {
    // 获取总数
    $total = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();

    // 处理分页边界
    $max_page = max(1, ceil($total / $per_page));
    $current_page = min($current_page, $max_page);

    if ($mode === 'review') {
        $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id ASC");
        $questions = $stmt->fetchAll();
    } elseif ($mode === 'sequential') {
        $offset = ($current_page - 1) * $per_page;
        
        $stmt = $pdo->prepare("SELECT * FROM `$table` ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $questions = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY RAND() LIMIT 10");
        $questions = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("获取题目失败: " . $e->getMessage());
}

// 初始化题目序号
$questionNumber = ($mode === 'sequential') ? $current_page : 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($type) ?>练习</title>
    <link href="status/bootstrap.min.css" rel="stylesheet">
    <style>
        .difficulty-badge {
            position: absolute;
            right: 1rem;
            top: 1rem;
            z-index: 2;
        }
        .question-card {
            position: relative;
            border-left: 4px solid #0d6efd;
            margin-bottom: 1.5rem;
        }
        .pagination-nav {
            margin: 2rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }
        .correct-highlight {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
        }
        .btn-correct {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
        }
        .btn-incorrect {
            background-color: #dc3545 !important;
            color: white !important;
            border-color: #dc3545 !important;
        }
        .pagination-nav .form-control {
            width: 80px;
        }
        /* 判断题样式 */
.btn-answer {
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.btn-check:checked + .btn-answer {
    transform: scale(0.98);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.btn-check:focus + .btn-answer {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.btn-check:checked + .btn-success {
    background-image: linear-gradient(to bottom right, #198754, #157347);
}

.btn-check:checked + .btn-danger {
    background-image: linear-gradient(to bottom right, #dc3545, #bb2d3b);
}

.btn-grid .badge {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="fas fa-book-open me-2"></i><?= ucfirst($type) ?>练习
                <span class="badge bg-secondary"><?= ['random'=>'随机','sequential'=>'顺序','review'=>'背题'][$mode] ?>模式</span>
            </h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>返回
            </a>
        </div>

        <?php if ($mode === 'sequential'): ?>
        <div class="pagination-nav shadow-sm">
            <div class="row align-items-center">
                <div class="col-auto">
                    <a href="?type=<?= $type ?>&mode=sequential&page=<?= max(1, $current_page-1) ?>" 
                        class="btn btn-outline-primary <?= $current_page <= 1 ? 'disabled' : '' ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </div>
                
                <div class="col">
                    <form method="get" class="input-group">
                        <input type="hidden" name="type" value="<?= $type ?>">
                        <input type="hidden" name="mode" value="sequential">
                        <input type="number" name="page" class="form-control" 
                               min="1" max="<?= $total ?>" value="<?= $current_page ?>">
                        <button type="submit" class="btn btn-primary">跳转</button>
                    </form>
                </div>

                <div class="col-auto">
                    <a href="?type=<?= $type ?>&mode=sequential&page=<?= max(1, $current_page-1) ?>" 
                        class="btn btn-outline-primary <?= $current_page <= 1 ? 'disabled' : '' ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="text-center mt-2">
                第 <?= $current_page ?> 题 / 共 <?= $total ?> 题
            </div>
        </div>
        <?php endif; ?>

        <form action="submit.php" method="post">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="hidden" name="mode" value="<?= $mode ?>">
            <input type="hidden" name="page" value="<?= $current_page ?>">

            <?php foreach ($questions as $q): ?>
            <div class="card question-card shadow-sm">
                <div class="card-body">
                    <?php
                    // 难度颜色处理
                    $textColor = match($q['difficulty']) {
                        'easy' => 'text-success',
                        'medium' => 'text-warning',
                        'hard' => 'text-danger',
                        default => 'text-secondary'
                    };
                    ?>
                    <!--<div class="difficulty-badge">-->
                    <!--    <span class="<?= $textColor ?> fw-bold">-->
                    <!--        难度：<?= ucfirst($q['difficulty']) ?>-->
                    <!--    </span>-->
                    <!--</div>-->
                    
                    <h5 class="card-title mb-4">
                        <span class="text-muted me-2">第<?= $questionNumber ?>题</span>
                        <?= htmlspecialchars($q['question']) ?>
                    </h5>

                    <?php if (in_array($type, ['single', 'multiple'])): ?>
    <div class="options">
        <?php 
        $correctAnswers = ($mode === 'review') ? explode(',', $q['answer']) : [];
        foreach (['a','b','c','d','e','f'] as $opt): 
            if (!empty($q["option_$opt"])):
                $isCorrect = in_array(strtoupper($opt), array_map('strtoupper', $correctAnswers));
                $inputId = 'q'.$q['id'].'_'.$opt; // 生成唯一ID
        ?>
                <div class="form-check ps-0 mb-2">
                    <!-- 调整input和label结构 -->
                    <input type="<?= $type === 'single' ? 'radio' : 'checkbox' ?>" 
                           class="form-check-input"
                           id="<?= $inputId ?>"
                           name="answers[<?= $q['id'] ?>]<?= $type === 'multiple' ? '[]' : '' ?>" 
                           value="<?= $opt ?>"
                           <?= $mode === 'review' ? 'disabled' : '' ?>>
                    <label class="form-check-label d-block p-2 rounded <?= $isCorrect ? 'correct-highlight' : '' ?>" 
                           for="<?= $inputId ?>">
                        <span class="ms-2 <?= $isCorrect ? 'text-success fw-bold' : '' ?>">
                            <?= strtoupper($opt) ?>. <?= htmlspecialchars($q["option_$opt"]) ?>
                        </span>
                    </label>
                </div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>

                    <?php elseif ($type === 'truefalse'): ?>
<?php 
$correctAnswer = ($mode === 'review') ? strtolower($q['answer']) : '';
$trueChecked = ($correctAnswer === 'true') ? 'checked' : '';
$falseChecked = ($correctAnswer === 'false') ? 'checked' : '';
$disabled = ($mode === 'review') ? 'disabled' : '';
?>
<div class="btn-grid">
    <div class="row g-3">
        <!-- 正确选项 -->
        <div class="col-12 col-md-6">
            <input type="radio" 
                   class="btn-check" 
                   name="answers[<?= $q['id'] ?>]" 
                   id="q<?= $q['id'] ?>_true" 
                   value="true"
                   <?= $trueChecked ?>
                   <?= $disabled ?>>
            <label class="btn btn-answer btn-success w-100 py-3" 
                   for="q<?= $q['id'] ?>_true">
                <i class="fas fa-check-circle me-2"></i>正确
                <?php if($correctAnswer === 'true'): ?>
                <span class="badge bg-white text-success ms-2">正确答案</span>
                <?php endif; ?>
            </label>
        </div>
        
        <!-- 错误选项 -->
        <div class="col-12 col-md-6">
            <input type="radio" 
                   class="btn-check" 
                   name="answers[<?= $q['id'] ?>]" 
                   id="q<?= $q['id'] ?>_false" 
                   value="false"
                   <?= $falseChecked ?>
                   <?= $disabled ?>>
            <label class="btn btn-answer btn-danger w-100 py-3" 
                   for="q<?= $q['id'] ?>_false">
                <i class="fas fa-times-circle me-2"></i>错误
                <?php if($correctAnswer === 'false'): ?>
                <span class="badge bg-white text-danger ms-2">正确答案</span>
                <?php endif; ?>
            </label>
        </div>
    </div>
</div>

                    <?php elseif ($type === 'fill'): ?>
                        <div class="input-group">
                            <span class="input-group-text">答案</span>
                            <input type="text" 
                                   name="answers[<?= $q['id'] ?>]" 
                                   class="form-control <?= ($mode === 'review') ? 'border-success' : '' ?>" 
                                   placeholder="请输入答案"
                                   <?= $mode === 'review' ? 'disabled' : '' ?>
                                   value="<?= ($mode === 'review') ? htmlspecialchars($q['answer']) : '' ?>">
                            <?php if ($mode === 'review'): ?>
                                <span class="input-group-text bg-success text-white">
                                    <i class="fas fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php 
            $questionNumber++;
            endforeach; 
            ?>

            <?php if ($mode !== 'review'): ?>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-paper-plane me-2"></i>提交答案
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="status/bootstrap.bundle.min.js"></script>
</body>
</html>
