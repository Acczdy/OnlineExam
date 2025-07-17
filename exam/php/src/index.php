<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>临时-考试练习</title>
    <link href="status/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-hover {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .type-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-5 display-4 fw-bold text-primary">
            <i class="fas fa-graduation-cap"></i> 考试练习
        </h1>
        
        <div class="row g-4">
            
            <!-- 单选题 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-hover border-primary h-100">
                    <div class="card-body text-center">
                        <div class="type-icon text-primary">
                            <i class="fas fa-dot-circle"></i>
                        </div>
                        <h2>
                            <a href="status/单选.pdf">单选题PDF</a> 
                        </h2>
                        <h3 class="card-title mb-3">单选题</h3>
                        <div class="d-grid gap-2">
                            <a href="practice.php?type=single&mode=random" 
                               class="btn btn-outline-primary btn-lg">随机练习</a>
                            <a href="practice.php?type=single&mode=sequential" 
                               class="btn btn-primary btn-lg">顺序练习</a>
                               <a href="practice.php?type=single&mode=review" class="btn btn-info btn-lg">背题模式</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 多选题 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-hover border-success h-100">
                    <div class="card-body text-center">
                        <div class="type-icon text-success">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h2>
                            <a href="status/多选.pdf">多选题PDF</a> 
                        </h2>
                        <h3 class="card-title mb-3">多选题</h3>
                        <div class="d-grid gap-2">
                            <a href="practice.php?type=multiple&mode=random" 
                               class="btn btn-outline-success btn-lg">随机练习</a>
                            <a href="practice.php?type=multiple&mode=sequential" 
                               class="btn btn-success btn-lg">顺序练习</a>
                               <a href="practice.php?type=multiple&mode=review" class="btn btn-info btn-lg">背题模式</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 判断题 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-hover border-warning h-100">
                    <div class="card-body text-center">
                        <div class="type-icon text-warning">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h2>
                            <a href="status/判断题.pdf">判断题PDF</a> 
                        </h2>
                        <h3 class="card-title mb-3">判断题</h3>
                        <div class="d-grid gap-2">
                            <a href="practice.php?type=truefalse&mode=random" 
                               class="btn btn-outline-warning btn-lg">随机练习</a>
                            <a href="practice.php?type=truefalse&mode=sequential" 
                               class="btn btn-warning btn-lg">顺序练习</a>
                               <a href="practice.php?type=truefalse&mode=review" class="btn btn-info btn-lg">背题模式</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 填空题 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-hover border-danger h-100">
                    <div class="card-body text-center">
                        <div class="type-icon text-danger">
                            <i class="fas fa-pen"></i>
                        </div>
                        <h2>
                            <a href="status/填空题.pdf">填空题PDF</a> 
                        </h2>
                        <h3 class="card-title mb-3">填空题</h3>
                        <div class="d-grid gap-2">
                            <a href="practice.php?type=fill&mode=random" 
                               class="btn btn-outline-danger btn-lg">随机练习</a>
                            <a href="practice.php?type=fill&mode=sequential" 
                               class="btn btn-danger btn-lg">顺序练习</a>
                               <a href="practice.php?type=fill&mode=review" class="btn btn-info btn-lg">背题模式</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="status/bootstrap.bundle.min.js"></script>
</body>
</html>
