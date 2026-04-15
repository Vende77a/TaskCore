<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TaskCore</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: #111827;
        }

        a {
            text-decoration: none;
        }

        .container {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
            background: rgba(248, 250, 252, 0.8);
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
        }

        .site-header-inner {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            color: #111827;
        }

        .brand-logo-wrap {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            flex-shrink: 0;
            padding: 4px;
        }

        .brand-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-link,
        .header-btn,
        .primary-btn,
        .secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 11px 16px;
            font-weight: 600;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .header-link {
            color: #374151;
        }

        .header-link:hover {
            color: #111827;
        }

        .header-btn,
        .secondary-btn {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .header-btn:hover,
        .secondary-btn:hover {
            background: #dbeafe;
        }

        .primary-btn {
            background: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(29, 78, 216, 0.25);
        }

        .primary-btn:hover {
            transform: translateY(-1px);
        }

        .hero {
            padding: 72px 0 48px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 28px;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .hero h1 {
            margin: 0;
            font-size: 56px;
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .hero p {
            margin: 20px 0 0 0;
            font-size: 18px;
            line-height: 1.7;
            color: #4b5563;
            max-width: 700px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        .hero-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .preview-window {
            border-radius: 20px;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
            border: 1px solid #dbeafe;
            padding: 20px;
        }

        .preview-topbar {
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
        }

        .preview-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #bfdbfe;
        }

        .preview-columns {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .preview-column {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 12px;
        }

        .preview-column-title {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .preview-task {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 8px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        }

        .preview-task:last-child {
            margin-bottom: 0;
        }

        .preview-line {
            height: 8px;
            border-radius: 999px;
            background: #cbd5e1;
            margin-bottom: 8px;
        }

        .preview-line.short {
            width: 58%;
            margin-bottom: 0;
        }

        .features {
            padding: 28px 0 72px;
        }

        .section-title {
            font-size: 34px;
            font-weight: 800;
            margin: 0 0 12px 0;
            text-align: center;
        }

        .section-subtitle {
            margin: 0 auto 32px;
            max-width: 760px;
            text-align: center;
            color: #6b7280;
            line-height: 1.7;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #dbeafe;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .feature-card h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
        }

        .feature-card p {
            margin: 0;
            color: #6b7280;
            line-height: 1.65;
            font-size: 14px;
        }

        .cta {
            padding: 0 0 80px;
        }

        .cta-box {
            background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
            color: white;
            border-radius: 28px;
            padding: 34px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: center;
            box-shadow: 0 18px 44px rgba(30, 58, 138, 0.22);
        }

        .cta-box h2 {
            margin: 0 0 10px 0;
            font-size: 30px;
        }

        .cta-box p {
            margin: 0;
            color: rgba(255,255,255,0.9);
            line-height: 1.65;
        }

        .footer {
            padding: 0 0 36px;
            color: #6b7280;
            font-size: 14px;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .hero-grid,
            .features-grid,
            .cta-box {
                grid-template-columns: 1fr;
            }

            .hero-grid {
                display: grid;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .cta-box {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }

            .hero h1 {
                font-size: 44px;
            }
        }

        @media (max-width: 720px) {
            .site-header-inner {
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                padding: 14px 0;
            }

            .header-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .hero {
                padding-top: 44px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 16px;
            }

            .preview-columns,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 28px;
            }

            .cta-box h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container site-header-inner">
        <a href="/" class="brand">
            <div class="brand-logo-wrap">
                <img
                    src="/images/taskcore-logo.png"
                    alt="TaskCore logo"
                    class="brand-logo"
                >
            </div>

            <div>
                <div class="brand-title">TaskCore</div>
                <div class="brand-subtitle">Project & Task Management</div>
            </div>
        </a>

        <div class="header-actions">
            @if ($canLogin)
                <a href="{{ route('login') }}" class="header-link">Войти</a>

                @if ($canRegister)
                    <a href="{{ route('register') }}" class="header-btn">Регистрация</a>
                @endif
            @endif
        </div>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="hero-badge">TaskCore • Kanban • Projects • Workflow</div>

                <h1>Управляй проектами и задачами в одном удобном пространстве</h1>

                <p>
                    TaskCore помогает организовывать проекты, распределять задачи,
                    отслеживать прогресс команды, работать с комментариями,
                    файлами и канбан-доской в едином интерфейсе.
                </p>

                <div class="hero-actions">
                    @if ($canLogin)
                        <a href="{{ route('login') }}" class="primary-btn">Войти в систему</a>
                    @endif

                    @if ($canRegister)
                        <a href="{{ route('register') }}" class="secondary-btn">Создать аккаунт</a>
                    @endif
                </div>
            </div>

            <div class="hero-card">
                <div class="preview-window">
                    <div class="preview-topbar">
                        <div class="preview-dot"></div>
                        <div class="preview-dot"></div>
                        <div class="preview-dot"></div>
                    </div>

                    <div class="preview-columns">
                        <div class="preview-column">
                            <div class="preview-column-title">Backlog</div>
                            <div class="preview-task">
                                <div class="preview-line"></div>
                                <div class="preview-line short"></div>
                            </div>
                            <div class="preview-task">
                                <div class="preview-line"></div>
                                <div class="preview-line short"></div>
                            </div>
                        </div>

                        <div class="preview-column">
                            <div class="preview-column-title">In Progress</div>
                            <div class="preview-task">
                                <div class="preview-line"></div>
                                <div class="preview-line short"></div>
                            </div>
                        </div>

                        <div class="preview-column">
                            <div class="preview-column-title">Review</div>
                            <div class="preview-task">
                                <div class="preview-line"></div>
                                <div class="preview-line short"></div>
                            </div>
                        </div>

                        <div class="preview-column">
                            <div class="preview-column-title">Done</div>
                            <div class="preview-task">
                                <div class="preview-line"></div>
                                <div class="preview-line short"></div>
                            </div>
                            <div class="preview-task">
                                <div class="preview-line"></div>
                                <div class="preview-line short"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="section-title">Что умеет TaskCore</h2>
            <p class="section-subtitle">
                Система уже покрывает ключевые сценарии управления задачами и
                закладывает основу для полноценной командной работы.
            </p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">01</div>
                    <h3>Проекты</h3>
                    <p>
                        Создавай проекты, описывай контекст работы и веди все задачи
                        внутри одной общей структуры.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">02</div>
                    <h3>Канбан-доска</h3>
                    <p>
                        Перемещай карточки между колонками, управляй статусами и
                        визуально отслеживай текущий прогресс.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">03</div>
                    <h3>Комментарии и файлы</h3>
                    <p>
                        Обсуждай задачи внутри карточки, прикрепляй материалы и
                        храни историю работы в одном месте.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">04</div>
                    <h3>Единая рабочая зона</h3>
                    <p>
                        Dashboard, проекты и задачи соединены в один интерфейс,
                        где удобно переключаться между разделами.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-box">
                <div>
                    <h2>Начни работу в TaskCore</h2>
                    <p>
                        Войди в систему, перейди к dashboard и управляй своими
                        проектами через единый интерфейс.
                    </p>
                </div>

                <div class="hero-actions">
                    @if ($canLogin)
                        <a href="{{ route('login') }}" class="primary-btn">Перейти ко входу</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        TaskCore — система управления проектами и задачами
    </div>
</footer>
</body>
</html>
