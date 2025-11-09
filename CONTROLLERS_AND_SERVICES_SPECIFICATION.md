# 📋 Спецификация контроллеров и сервисов для проекта Klust

## 📝 Общие принципы

- Все контроллеры должны использовать **Form Request** классы для валидации
- Бизнес-логика выносится в **Service классы**
- Контроллеры отвечают только за HTTP-запросы/ответы и координацию
- Использовать **Dependency Injection** для сервисов
- Все методы должны использовать `declare(strict_types=1);`
- Возвращать `Inertia::render()` для страниц и `redirect()` для действий

---

## 🔐 Auth (Аутентификация)

### 📁 `app/Http/Controllers/Auth/`

#### ✅ `LoginController.php` - Уже создан
- `show(): Inertia\Response` - показать форму входа
- `login(LoginRequest $request): RedirectResponse` - обработать вход

#### ✅ `RegisterController.php` - Уже создан
- `show(): Inertia\Response` - показать форму регистрации
- `registerStudent(RegisterStudentRequest $request): RedirectResponse` - регистрация студента
- `registerPartner(RegisterPartnerRequest $request): RedirectResponse` - регистрация партнера

#### ✅ `LogoutController.php` - Уже создан
- `logout(Request $request): RedirectResponse` - выход

---

## 👥 Admin (Админка)

### 📁 `app/Http/Controllers/Admin/`

#### `DashboardController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить статистику через `DashboardService::getStatistics()`
- Подсчитать общее количество студентов, партнеров, кейсов
- Получить последние активности (новые регистрации, кейсы)
- Вернуть `Inertia::render('Admin/Dashboard', ['statistics' => $stats])`

**Middleware:** `auth`, `role:admin|teacher`

---

#### `UserController.php`

##### `index(Request $request): Inertia\Response`
**Параметры:**
- `Request $request` - для фильтров и поиска

**Что внутри:**
- Получить фильтры из запроса (role, status, search)
- Получить пользователей через `UserService::getFilteredUsers($filters)`
- Применить пагинацию
- Вернуть `Inertia::render('Admin/Users/Index', ['users' => $users, 'filters' => $filters])`

---

##### `show(User $user): Inertia\Response`
**Параметры:**
- `User $user` - пользователь из route model binding

**Что внутри:**
- Загрузить все связи (eager load): профили, навыки, бейджи, кейсы
- Получить полную информацию через `UserService::getUserDetails($user)`
- Вернуть `Inertia::render('Admin/Users/Show', ['user' => $user])`

---

##### `create(): Inertia\Response`
**Что внутри:**
- Получить список всех навыков и бейджей для формы
- Вернуть `Inertia::render('Admin/Users/Create', ['skills' => $skills, 'badges' => $badges])`

---

##### `store(Admin\User\StoreRequest $request): RedirectResponse`
**Параметры:**
- `Admin\User\StoreRequest $request` - валидированные данные

**Что внутри:**
- Использовать `UserService::createUser($request->validated())`
- Создать пользователя и профиль в транзакции
- Назначить роль через `$user->assignRole($request->role)`
- Вернуть `redirect()->route('admin.users.index')->with('success', ...)`

---

##### `edit(User $user): Inertia\Response`
**Параметры:**
- `User $user` - пользователь

**Что внутри:**
- Загрузить пользователя со связями
- Вернуть `Inertia::render('Admin/Users/Edit', ['user' => $user])`

---

##### `update(Admin\User\UpdateRequest $request, User $user): RedirectResponse`
**Параметры:**
- `Admin\User\UpdateRequest $request` - валидированные данные
- `User $user` - пользователь

**Что внутри:**
- Использовать `UserService::updateUser($user, $request->validated())`
- Обновить пользователя и профиль в транзакции
- Обработать загрузку аватара (если есть)
- Вернуть `redirect()->route('admin.users.show', $user)->with('success', ...)`

---

##### `destroy(User $user): RedirectResponse`
**Параметры:**
- `User $user` - пользователь

**Что внутри:**
- Проверить права (только admin может удалять)
- Использовать `UserService::deleteUser($user)` (мягкое удаление)
- Вернуть `redirect()->route('admin.users.index')->with('success', ...)`

**Middleware:** `auth`, `role:admin`

---

#### `CaseController.php`

##### `index(Request $request): Inertia\Response`
**Параметры:**
- `Request $request` - для фильтров

**Что внутри:**
- Получить фильтры (status, partner_id, search)
- Получить кейсы через `CaseService::getFilteredCases($filters)`
- Применить пагинацию с eager load (partner, skills)
- Вернуть `Inertia::render('Admin/Cases/Index', ['cases' => $cases])`

---

##### `show(CaseModel $case): Inertia\Response`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Загрузить связи: partner, skills, applications, teams
- Получить статистику через `CaseService::getCaseStatistics($case)`
- Вернуть `Inertia::render('Admin/Cases/Show', ['case' => $case])`

---

##### `create(): Inertia\Response`
**Что внутри:**
- Получить список партнеров и симуляторов
- Получить список всех навыков
- Вернуть `Inertia::render('Admin/Cases/Create', ['partners' => $partners, 'skills' => $skills])`

---

##### `store(Admin\Case\StoreRequest $request): RedirectResponse`
**Параметры:**
- `Admin\Case\StoreRequest $request` - валидированные данные

**Что внутри:**
- Использовать `CaseService::createCase($request->validated())`
- Создать кейс и привязать навыки через sync
- Вернуть `redirect()->route('admin.cases.show', $case)->with('success', ...)`

---

##### `edit(CaseModel $case): Inertia\Response`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Загрузить кейс со связанными навыками
- Получить список партнеров и симуляторов
- Вернуть `Inertia::render('Admin/Cases/Edit', ['case' => $case])`

---

##### `update(Admin\Case\UpdateRequest $request, CaseModel $case): RedirectResponse`
**Параметры:**
- `Admin\Case\UpdateRequest $request` - валидированные данные
- `CaseModel $case` - кейс

**Что внутри:**
- Использовать `CaseService::updateCase($case, $request->validated())`
- Обновить навыки через sync
- Вернуть `redirect()->route('admin.cases.show', $case)->with('success', ...)`

---

##### `destroy(CaseModel $case): RedirectResponse`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить, нет ли активных заявок
- Использовать `CaseService::deleteCase($case)`
- Вернуть `redirect()->route('admin.cases.index')->with('success', ...)`

---

#### `SkillController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить все навыки с подсчетом использования
- Вернуть `Inertia::render('Admin/Skills/Index', ['skills' => $skills])`

---

##### `store(Admin\Skill\StoreRequest $request): RedirectResponse`
**Параметры:**
- `Admin\Skill\StoreRequest $request` - валидированные данные

**Что внутри:**
- Использовать `SkillService::createSkill($request->validated())`
- Вернуть `redirect()->route('admin.skills.index')->with('success', ...)`

---

##### `update(Admin\Skill\UpdateRequest $request, Skill $skill): RedirectResponse`
**Параметры:**
- `Admin\Skill\UpdateRequest $request` - валидированные данные
- `Skill $skill` - навык

**Что внутри:**
- Использовать `SkillService::updateSkill($skill, $request->validated())`
- Вернуть `redirect()->route('admin.skills.index')->with('success', ...)`

---

##### `destroy(Skill $skill): RedirectResponse`
**Параметры:**
- `Skill $skill` - навык

**Что внутри:**
- Проверить, используется ли навык в кейсах или у пользователей
- Использовать `SkillService::deleteSkill($skill)`
- Вернуть `redirect()->route('admin.skills.index')->with('success', ...)`

---

#### `BadgeController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить все бейджи с подсчетом полученных
- Вернуть `Inertia::render('Admin/Badges/Index', ['badges' => $badges])`

---

##### `store(Admin\Badge\StoreRequest $request): RedirectResponse`
**Параметры:**
- `Admin\Badge\StoreRequest $request` - валидированные данные

**Что внутри:**
- Использовать `BadgeService::createBadge($request->validated())`
- Обработать загрузку иконки через `FileService::storeBadgeIcon($request->file('icon'))`
- Вернуть `redirect()->route('admin.badges.index')->with('success', ...)`

---

##### `update(Admin\Badge\UpdateRequest $request, Badge $badge): RedirectResponse`
**Параметры:**
- `Admin\Badge\UpdateRequest $request` - валидированные данные
- `Badge $badge` - бейдж

**Что внутри:**
- Использовать `BadgeService::updateBadge($badge, $request->validated())`
- Обработать обновление иконки (если есть)
- Вернуть `redirect()->route('admin.badges.index')->with('success', ...)`

---

##### `destroy(Badge $badge): RedirectResponse`
**Параметры:**
- `Badge $badge` - бейдж

**Что внутри:**
- Использовать `BadgeService::deleteBadge($badge)`
- Удалить иконку файла
- Вернуть `redirect()->route('admin.badges.index')->with('success', ...)`

---

#### `SimulatorController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить все симуляторы с партнерами
- Вернуть `Inertia::render('Admin/Simulators/Index', ['simulators' => $simulators])`

---

##### `store(Admin\Simulator\StoreRequest $request): RedirectResponse`
**Параметры:**
- `Admin\Simulator\StoreRequest $request` - валидированные данные

**Что внутри:**
- Использовать `SimulatorService::createSimulator($request->validated())`
- Обработать загрузку preview_image
- Вернуть `redirect()->route('admin.simulators.index')->with('success', ...)`

---

##### `update(Admin\Simulator\UpdateRequest $request, Simulator $simulator): RedirectResponse`
**Параметры:**
- `Admin\Simulator\UpdateRequest $request` - валидированные данные
- `Simulator $simulator` - симулятор

**Что внутри:**
- Использовать `SimulatorService::updateSimulator($simulator, $request->validated())`
- Обработать обновление preview_image
- Вернуть `redirect()->route('admin.simulators.index')->with('success', ...)`

---

##### `destroy(Simulator $simulator): RedirectResponse`
**Параметры:**
- `Simulator $simulator` - симулятор

**Что внутри:**
- Проверить, нет ли активных сессий
- Использовать `SimulatorService::deleteSimulator($simulator)`
- Вернуть `redirect()->route('admin.simulators.index')->with('success', ...)`

---

## 🎓 Student (Студент)

### 📁 `app/Http/Controllers/Client/Student/`

#### `DashboardController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить статистику студента через `StudentService::getDashboardStatistics(auth()->user())`
- Получить активные кейсы студента
- Получить рекомендации через `CaseService::getRecommendedCases($user)`
- Получить последние достижения (бейджи, навыки)
- Вернуть `Inertia::render('Client/Student/Dashboard', ['statistics' => $stats, ...])`

---

#### `CasesController.php`

##### `index(Request $request): Inertia\Response`
**Параметры:**
- `Request $request` - для фильтров

**Что внутри:**
- Получить фильтры (skills, partner_id, search)
- Получить доступные кейсы через `CaseService::getAvailableCasesForStudent($user, $filters)`
- Исключить кейсы, на которые уже подана заявка
- Применить пагинацию
- Вернуть `Inertia::render('Client/Student/Cases/Index', ['cases' => $cases])`

---

##### `show(CaseModel $case): Inertia\Response`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить, что кейс имеет статус 'active'
- Загрузить связи: partner, skills
- Получить статус заявки студента через `ApplicationService::getStudentApplicationStatus($user, $case)`
- Вернуть `Inertia::render('Client/Student/Cases/Show', ['case' => $case, 'applicationStatus' => $status])`

---

##### `myCases(): Inertia\Response`
**Что внутри:**
- Получить текущие кейсы (в команде, статус accepted)
- Получить заявки (pending)
- Получить завершенные (completed)
- Получить отклоненные (rejected)
- Через `ApplicationService::getStudentCasesGrouped($user)`
- Вернуть `Inertia::render('Client/Student/Cases/MyCases', ['cases' => $groupedCases])`

---

##### `apply(Student\Case\ApplyRequest $request, CaseModel $case): RedirectResponse`
**Параметры:**
- `Student\Case\ApplyRequest $request` - валидированные данные
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить, что студент не подал уже заявку через `ApplicationService::hasApplication($user, $case)`
- Проверить, что кейс активен и дедлайн не прошел
- Проверить, что все team_members - студенты
- Проверить размер команды (лидер + участники <= required_team_size)
- Использовать `ApplicationService::createApplication($user, $case, $request->validated())`
- Создать заявку и участников команды
- Отправить уведомление партнеру через `NotificationService::notifyPartnerAboutApplication($application)`
- Вернуть `redirect()->route('student.cases.show', $case)->with('success', ...)`

---

##### `addTeamMember(Student\Case\AddTeamMemberRequest $request, CaseApplication $application): RedirectResponse`
**Параметры:**
- `Student\Case\AddTeamMemberRequest $request` - валидированные данные
- `CaseApplication $application` - заявка

**Что внутри:**
- Проверить, что заявка принадлежит студенту и имеет статус 'pending'
- Проверить, что user_id - студент
- Проверить размер команды (не превышает required_team_size)
- Проверить, что студент не находится уже в другой команде на этом кейсе
- Использовать `ApplicationService::addTeamMember($application, $request->user_id)`
- Вернуть `redirect()->route('student.cases.my')->with('success', ...)`

---

##### `withdraw(CaseApplication $application): RedirectResponse`
**Параметры:**
- `CaseApplication $application` - заявка

**Что внутри:**
- Проверить права (только лидер заявки может отозвать)
- Использовать `ApplicationService::withdrawApplication($application)`
- Вернуть `redirect()->route('student.cases.my')->with('success', ...)`

---

##### `team(CaseApplication $application): Inertia\Response`
**Параметры:**
- `CaseApplication $application` - заявка (должна быть accepted)

**Что внутри:**
- Проверить, что студент входит в команду
- Загрузить команду со всеми участниками
- Получить прогресс команды через `TeamService::getTeamProgress($application)`
- Вернуть `Inertia::render('Client/Student/Cases/Team', ['team' => $team, 'progress' => $progress])`

---

#### `ProfileController.php`

##### `show(): Inertia\Response`
**Что внутри:**
- Получить текущего пользователя с профилем
- Вернуть `Inertia::render('Client/Student/Profile/Index', ['user' => $user])`

---

##### `edit(): Inertia\Response`
**Что внутри:**
- Получить текущего пользователя с профилем
- Вернуть `Inertia::render('Client/Student/Profile/Edit', ['user' => $user])`

---

##### `update(Student\Profile\UpdateRequest $request): RedirectResponse`
**Параметры:**
- `Student\Profile\UpdateRequest $request` - валидированные данные

**Что внутри:**
- Использовать `UserService::updateStudentProfile(auth()->user(), $request->validated())`
- Обработать загрузку аватара через `FileService::storeAvatar($request->file('avatar'))`
- Обновить User и StudentProfile
- Вернуть `redirect()->route('student.profile.show')->with('success', ...)`

---

#### `SkillsController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить все навыки студента с уровнями через `SkillService::getStudentSkills($user)`
- Получить историю получения очков через `ProgressLogService::getSkillProgressHistory($user)`
- Вернуть `Inertia::render('Client/Student/Skills/Index', ['skills' => $skills, 'history' => $history])`

---

#### `BadgesController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить полученные бейджи через `BadgeService::getStudentBadges($user)`
- Получить условия получения следующих бейджей
- Вернуть `Inertia::render('Client/Student/Badges/Index', ['badges' => $badges, 'upcoming' => $upcoming])`

---

#### `SimulatorsController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить доступные симуляторы (is_active = true)
- Получить историю сессий студента
- Вернуть `Inertia::render('Client/Student/Simulators/Index', ['simulators' => $simulators, 'sessions' => $sessions])`

---

##### `start(Simulator $simulator): RedirectResponse`
**Параметры:**
- `Simulator $simulator` - симулятор

**Что внутри:**
- Проверить, что симулятор активен
- Проверить, нет ли активной сессии через `SimulatorService::hasActiveSession($user, $simulator)`
- Использовать `SimulatorService::startSession($user, $simulator)`
- Вернуть `redirect()->route('student.simulators.session', $session)->with('success', ...)`

---

##### `session(SimulatorSession $session): Inertia\Response`
**Параметры:**
- `SimulatorSession $session` - сессия

**Что внутри:**
- Проверить, что сессия принадлежит студенту
- Загрузить симулятор и данные сессии
- Вернуть `Inertia::render('Client/Student/Simulators/Session', ['session' => $session])`

---

##### `complete(Student\Simulator\CompleteRequest $request, SimulatorSession $session): RedirectResponse`
**Параметры:**
- `Student\Simulator\CompleteRequest $request` - валидированные данные (score, time_spent, answers)
- `SimulatorSession $session` - сессия

**Что внутри:**
- Использовать `SimulatorService::completeSession($session, $request->validated())`
- Начислить очки по навыкам на основе score
- Обновить прогресс студента через `ProgressLogService::logSimulatorCompletion($session)`
- Вернуть `redirect()->route('student.simulators.index')->with('success', ...)`

---

## 🏢 Partner (Партнер)

### 📁 `app/Http/Controllers/Client/Partner/`

#### `DashboardController.php`

##### `index(): Inertia\Response`
**Что внутри:**
- Получить статистику партнера через `PartnerService::getDashboardStatistics(auth()->user())`
- Получить активные кейсы
- Получить последние активности (новые заявки, завершенные кейсы)
- Вернуть `Inertia::render('Client/Partner/Dashboard', ['statistics' => $stats, ...])`

---

#### `CasesController.php`

##### `index(Request $request): Inertia\Response`
**Параметры:**
- `Request $request` - для фильтров

**Что внутри:**
- Получить фильтры (status, search)
- Получить только кейсы партнера через `CaseService::getPartnerCases($partner, $filters)`
- Применить пагинацию
- Вернуть `Inertia::render('Client/Partner/Cases/Index', ['cases' => $cases])`

---

##### `create(): Inertia\Response`
**Что внутри:**
- Получить список всех навыков
- Получить список симуляторов партнера (опционально)
- Вернуть `Inertia::render('Client/Partner/Cases/Create', ['skills' => $skills])`

---

##### `store(Partner\Case\StoreRequest $request): RedirectResponse`
**Параметры:**
- `Partner\Case\StoreRequest $request` - валидированные данные

**Что внутри:**
- Получить партнера из auth()->user()
- Использовать `CaseService::createCaseForPartner($partner, $request->validated())`
- Привязать навыки через sync
- Если статус 'active', отправить уведомления студентам через `NotificationService::notifyStudentsAboutNewCase($case)`
- Вернуть `redirect()->route('partner.cases.show', $case)->with('success', ...)`

---

##### `show(CaseModel $case): Inertia\Response`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить права (только свой кейс) через `CaseService::ensureCaseBelongsToPartner($case, $partner)`
- Загрузить связи: skills, applications, teams
- Получить статистику через `CaseService::getCaseStatistics($case)`
- Вернуть `Inertia::render('Client/Partner/Cases/Show', ['case' => $case, 'applications' => $applications, 'teams' => $teams])`

---

##### `edit(CaseModel $case): Inertia\Response`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить права
- Загрузить кейс со связанными навыками
- Получить список навыков
- Вернуть `Inertia::render('Client/Partner/Cases/Edit', ['case' => $case, 'skills' => $skills])`

---

##### `update(Partner\Case\UpdateRequest $request, CaseModel $case): RedirectResponse`
**Параметры:**
- `Partner\Case\UpdateRequest $request` - валидированные данные
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить права
- Использовать `CaseService::updateCase($case, $request->validated())`
- Обновить навыки
- Вернуть `redirect()->route('partner.cases.show', $case)->with('success', ...)`

---

##### `archive(CaseModel $case): RedirectResponse`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить права
- Изменить статус на 'archived' через `CaseService::archiveCase($case)`
- Вернуть `redirect()->route('partner.cases.index')->with('success', ...)`

---

##### `applications(CaseModel $case, Request $request): Inertia\Response`
**Параметры:**
- `CaseModel $case` - кейс
- `Request $request` - для фильтров

**Что внутри:**
- Проверить права
- Получить заявки на кейс с фильтрацией по статусу
- Загрузить команды заявок
- Вернуть `Inertia::render('Client/Partner/Cases/Applications', ['case' => $case, 'applications' => $applications])`

---

#### `ApplicationController.php` (или методы в CasesController)

##### `approve(Partner\Application\ApproveRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse`
**Параметры:**
- `Partner\Application\ApproveRequest $request` - валидированные данные
- `CaseModel $case` - кейс
- `CaseApplication $application` - заявка

**Что внутри:**
- Проверить права (кейс принадлежит партнеру)
- Проверить, что заявка имеет статус 'pending'
- Использовать `ApplicationService::approveApplication($application, $request->comment)`
- Изменить статус на 'accepted'
- Отправить уведомления команде через `NotificationService::notifyTeamAboutApproval($application)`
- Вернуть `redirect()->back()->with('success', ...)`

---

##### `reject(Partner\Application\RejectRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse`
**Параметры:**
- `Partner\Application\RejectRequest $request` - валидированные данные
- `CaseModel $case` - кейс
- `CaseApplication $application` - заявка

**Что внутри:**
- Проверить права
- Проверить статус заявки
- Использовать `ApplicationService::rejectApplication($application, $request->rejection_reason)`
- Изменить статус на 'rejected'
- Отправить уведомление лидеру команды
- Вернуть `redirect()->back()->with('success', ...)`

---

#### `TeamController.php`

##### `index(Request $request): Inertia\Response`
**Параметры:**
- `Request $request` - для фильтров

**Что внутри:**
- Получить фильтры (case_id, status)
- Получить все команды партнера через `TeamService::getPartnerTeams($partner, $filters)`
- Загрузить кейсы и участников команд
- Вернуть `Inertia::render('Client/Partner/Teams/Index', ['teams' => $teams])`

---

##### `show(CaseApplication $application): Inertia\Response`
**Параметры:**
- `CaseApplication $application` - заявка (команда)

**Что внутри:**
- Проверить права (кейс принадлежит партнеру)
- Загрузить команду со всеми участниками
- Получить прогресс команды через `TeamService::getTeamProgress($application)`
- Получить историю активности
- Вернуть `Inertia::render('Client/Partner/Teams/Show', ['team' => $team, 'progress' => $progress])`

---

#### `ProfileController.php`

##### `show(): Inertia\Response`
**Что внутри:**
- Получить текущего пользователя с партнерским профилем
- Вернуть `Inertia::render('Client/Partner/Profile/Index', ['user' => $user])`

---

##### `edit(): Inertia\Response`
**Что внутри:**
- Получить текущего пользователя с профилем
- Вернуть `Inertia::render('Client/Partner/Profile/Edit', ['user' => $user])`

---

##### `update(Partner\Profile\UpdateRequest $request): RedirectResponse`
**Параметры:**
- `Partner\Profile\UpdateRequest $request` - валидированные данные

**Что внутри:**
- Использовать `UserService::updatePartnerProfile(auth()->user(), $request->validated())`
- Обработать загрузку логотипа через `FileService::storeLogo($request->file('logo'))`
- Обновить User, PartnerProfile и Partner
- Вернуть `redirect()->route('partner.profile.show')->with('success', ...)`

---

#### `AnalyticsController.php`

##### `index(Partner\Analytics\IndexRequest $request): Inertia\Response`
**Параметры:**
- `Partner\Analytics\IndexRequest $request` - валидированные фильтры

**Что внутри:**
- Получить аналитику через `AnalyticsService::getPartnerAnalytics($partner, $request->validated())`
- Подсчитать статистику по кейсам, командам, заявкам
- Построить графики (динамика, популярность, конверсия)
- Вернуть `Inertia::render('Client/Partner/Analytics/Index', ['analytics' => $analytics])`

---

## 🔧 Services (Сервисы - бизнес-логика)

### 📁 `app/Services/`

#### `UserService.php`

##### `createUser(array $data): User`
**Параметры:**
- `array $data` - данные пользователя

**Что внутри:**
- Начать транзакцию
- Создать пользователя
- Хешировать пароль
- Создать соответствующий профиль (StudentProfile/PartnerProfile/TeacherProfile) в зависимости от роли
- Назначить роль через Spatie Permission
- Загрузить аватар (если есть) через FileService
- Закоммитить транзакцию
- Вернуть созданного пользователя

---

##### `updateUser(User $user, array $data): User`
**Параметры:**
- `User $user` - пользователь
- `array $data` - данные для обновления

**Что внутри:**
- Начать транзакцию
- Обновить User
- Обновить соответствующий профиль
- Обновить пароль (если указан)
- Обновить аватар (если указан)
- Закоммитить транзакцию
- Вернуть обновленного пользователя

---

##### `deleteUser(User $user): bool`
**Параметры:**
- `User $user` - пользователь

**Что внутри:**
- Проверить наличие зависимостей (активные кейсы, заявки)
- Выполнить мягкое удаление (soft delete) или hard delete в зависимости от политики
- Вернуть true/false

---

##### `getFilteredUsers(array $filters): LengthAwarePaginator`
**Параметры:**
- `array $filters` - фильтры (role, status, search)

**Что внутри:**
- Построить запрос с учетом фильтров
- Применить поиск по name, email, kubgtu_id
- Применить фильтрацию по роли (через Spatie)
- Применить пагинацию
- Вернуть пагинированный результат

---

##### `getUserDetails(User $user): array`
**Параметры:**
- `User $user` - пользователь

**Что внутри:**
- Загрузить все связи: профили, навыки, бейджи, кейсы, заявки
- Подсчитать статистику (количество кейсов, очки, бейджи)
- Вернуть массив с данными пользователя

---

##### `updateStudentProfile(User $user, array $data): User`
**Параметры:**
- `User $user` - пользователь (студент)
- `array $data` - данные профиля

**Что внутри:**
- Обновить User
- Обновить StudentProfile
- Обработать аватар
- Вернуть пользователя

---

##### `updatePartnerProfile(User $user, array $data): User`
**Параметры:**
- `User $user` - пользователь (партнер)
- `array $data` - данные профиля

**Что внутри:**
- Обновить User
- Обновить PartnerProfile
- Обновить Partner (если есть)
- Обработать логотип
- Вернуть пользователя

---

#### `CaseService.php`

##### `createCase(array $data, ?Partner $partner = null): CaseModel`
**Параметры:**
- `array $data` - данные кейса
- `?Partner $partner` - партнер (опционально, если null - берется из auth)

**Что внутри:**
- Начать транзакцию
- Создать CaseModel
- Если указаны required_skills, синхронизировать через sync()
- Закоммитить транзакцию
- Вернуть созданный кейс

---

##### `createCaseForPartner(Partner $partner, array $data): CaseModel`
**Параметры:**
- `Partner $partner` - партнер
- `array $data` - данные кейса

**Что внутри:**
- Установить partner_id из партнера
- Вызвать createCase($data, $partner)
- Вернуть кейс

---

##### `updateCase(CaseModel $case, array $data): CaseModel`
**Параметры:**
- `CaseModel $case` - кейс
- `array $data` - данные для обновления

**Что внутри:**
- Обновить поля кейса
- Синхронизировать навыки (если указаны)
- Вернуть обновленный кейс

---

##### `deleteCase(CaseModel $case): bool`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить наличие активных заявок
- Удалить связи (skills, applications каскадно через БД)
- Удалить кейс
- Вернуть true/false

---

##### `archiveCase(CaseModel $case): CaseModel`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Изменить статус на 'archived'
- Сохранить
- Вернуть кейс

---

##### `getFilteredCases(array $filters): LengthAwarePaginator`
**Параметры:**
- `array $filters` - фильтры (status, partner_id, search)

**Что внутри:**
- Построить запрос с фильтрами
- Применить eager loading (partner, skills)
- Применить пагинацию
- Вернуть результат

---

##### `getAvailableCasesForStudent(User $user, array $filters): LengthAwarePaginator`
**Параметры:**
- `User $user` - студент
- `array $filters` - фильтры

**Что внутри:**
- Получить кейсы со статусом 'active' и дедлайном в будущем
- Исключить кейсы, на которые студент уже подал заявку
- Применить фильтрацию по навыкам (если указаны)
- Применить пагинацию
- Вернуть результат

---

##### `getPartnerCases(Partner $partner, array $filters): LengthAwarePaginator`
**Параметры:**
- `Partner $partner` - партнер
- `array $filters` - фильтры

**Что внутри:**
- Получить только кейсы партнера
- Применить фильтры
- Применить пагинацию
- Вернуть результат

---

##### `getActiveCasesForPartner(User $user): Collection`
**Параметры:**
- `User $user` - пользователь (партнер)

**Что внутри:**
- Получить Partner через user->partner
- Получить только активные кейсы партнера (status = 'active')
- Применить eager loading (skills, partner)
- Отсортировать по дате создания (новые первыми)
- Вернуть коллекцию (без пагинации, для dashboard)

---

##### `getRecommendedCases(User $user, int $limit = 5): Collection`
**Параметры:**
- `User $user` - студент
- `int $limit` - количество рекомендаций

**Что внутри:**
- Получить навыки студента
- Найти кейсы с совпадающими навыками
- Отсортировать по релевантности
- Вернуть коллекцию

---

##### `getCaseStatistics(CaseModel $case): array`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Подсчитать количество заявок по статусам
- Подсчитать количество команд
- Подсчитать средний размер команд
- Вернуть массив статистики

---

##### `ensureCaseBelongsToPartner(CaseModel $case, Partner $partner): void`
**Параметры:**
- `CaseModel $case` - кейс
- `Partner $partner` - партнер

**Что внутри:**
- Проверить, что case->partner_id === partner->id
- Выбросить исключение, если не совпадает

---

#### `ApplicationService.php`

##### `createApplication(User $leader, CaseModel $case, array $data): CaseApplication`
**Параметры:**
- `User $leader` - лидер команды (студент)
- `CaseModel $case` - кейс
- `array $data` - данные (motivation, team_members)

**Что внутри:**
- Начать транзакцию
- Создать CaseApplication с leader_id и motivation
- Если указаны team_members, создать записи в case_team_members
- Проверить, что все участники - студенты
- Проверить размер команды
- Установить submitted_at
- Закоммитить транзакцию
- Вернуть созданную заявку

---

##### `approveApplication(CaseApplication $application, ?string $comment = null): CaseApplication`
**Параметры:**
- `CaseApplication $application` - заявка
- `?string $comment` - комментарий (опционально)

**Что внутри:**
- Проверить статус заявки (должен быть 'pending')
- Изменить статус на 'accepted'
- Сохранить комментарий (если есть)
- Обновить заявку
- Вернуть заявку

---

##### `rejectApplication(CaseApplication $application, string $rejectionReason): CaseApplication`
**Параметры:**
- `CaseApplication $application` - заявка
- `string $rejectionReason` - причина отклонения

**Что внутри:**
- Проверить статус заявки
- Изменить статус на 'rejected'
- Сохранить причину отклонения
- Обновить заявку
- Вернуть заявку

---

##### `withdrawApplication(CaseApplication $application): bool`
**Параметры:**
- `CaseApplication $application` - заявка

**Что внутри:**
- Проверить, что заявка имеет статус 'pending'
- Удалить участников команды
- Удалить заявку
- Вернуть true

---

##### `addTeamMember(CaseApplication $application, int $userId): CaseTeamMember`
**Параметры:**
- `CaseApplication $application` - заявка
- `int $userId` - ID пользователя для добавления

**Что внутри:**
- Проверить, что заявка имеет статус 'pending'
- Проверить, что пользователь - студент
- Проверить размер команды
- Проверить, что студент не находится уже в другой команде на этом кейсе
- Создать запись в case_team_members
- Вернуть созданную запись

---

##### `hasApplication(User $user, CaseModel $case): bool`
**Параметры:**
- `User $user` - студент
- `CaseModel $case` - кейс

**Что внутри:**
- Проверить наличие заявки у студента на этот кейс
- Вернуть true/false

---

##### `getStudentApplicationStatus(User $user, CaseModel $case): ?CaseApplication`
**Параметры:**
- `User $user` - студент
- `CaseModel $case` - кейс

**Что внутри:**
- Найти заявку студента на кейс (как лидер или участник)
- Вернуть заявку или null

---

##### `getStudentCasesGrouped(User $user): array`
**Параметры:**
- `User $user` - студент

**Что внутри:**
- Получить текущие (accepted)
- Получить заявки (pending)
- Получить завершенные (completed)
- Получить отклоненные (rejected)
- Вернуть массив с группировкой

---

#### `StudentService.php`

##### `getDashboardStatistics(User $user): array`
**Параметры:**
- `User $user` - студент

**Что внутри:**
- Подсчитать общее количество очков из StudentProfile
- Подсчитать количество активных кейсов
- Подсчитать количество завершенных кейсов
- Подсчитать количество бейджей
- Подсчитать количество навыков
- Вернуть массив статистики

---

#### `PartnerService.php`

##### `getDashboardStatistics(User $user): array`
**Параметры:**
- `User $user` - партнер

**Что внутри:**
- Получить Partner через user->partner
- Подсчитать количество созданных кейсов
- Подсчитать количество активных кейсов
- Подсчитать количество завершенных кейсов
- Подсчитать количество команд
- Подсчитать среднюю оценку (если есть рейтинг)
- Вернуть массив статистики

---



#### `TeamService.php`

##### `getTeamProgress(CaseApplication $application): array`
**Параметры:**
- `CaseApplication $application` - заявка (команда)

**Что внутри:**
- Получить всех участников команды
- Подсчитать общий прогресс выполнения кейса
- Вернуть массив с прогрессом

---
##### `getTeamActivityHistory(CaseApplication $application): Collection`
**Параметры:**
- `CaseApplication $application` - заявка (команда)

**Что внутри:**
- Получить всех участников команды из case_team_members
- Получить записи ProgressLog для участников команды, связанные с кейсом
- Получить записи о завершении симуляторов участниками команды (если симулятор связан с кейсом)
- Объединить все активности в единую коллекцию
- Отсортировать по дате (новые первыми)
- Вернуть коллекцию с информацией о типе активности, участнике и дате

--- 

##### `getPartnerTeams(Partner $partner, array $filters): Collection`
**Параметры:**
- `Partner $partner` - партнер
- `array $filters` - фильтры

**Что внутри:**
- Получить все заявки на кейсы партнера со статусом 'accepted'
- Загрузить команды и участников
- Применить фильтры
- Вернуть коллекцию

---

#### `SkillService.php`

##### `createSkill(array $data): Skill`
**Параметры:**
- `array $data` - данные навыка

**Что внутри:**
- Создать навык
- Вернуть созданный навык

---

##### `updateSkill(Skill $skill, array $data): Skill`
**Параметры:**
- `Skill $skill` - навык
- `array $data` - данные

**Что внутри:**
- Обновить навык
- Вернуть обновленный навык

---

##### `deleteSkill(Skill $skill): bool`
**Параметры:**
- `Skill $skill` - навык

**Что внутри:**
- Проверить использование (кейсы, пользователи)
- Удалить навык
- Вернуть true/false

---

##### `getStudentSkills(User $user): Collection`
**Параметры:**
- `User $user` - студент

**Что внутри:**
- Получить навыки студента через pivot с уровнями и очками
- Отсортировать по уровню или очкам
- Вернуть коллекцию

---

#### `BadgeService.php`

##### `createBadge(array $data): Badge`
**Параметры:**
- `array $data` - данные бейджа

**Что внутри:**
- Обработать загрузку иконки через FileService
- Создать бейдж
- Вернуть созданный бейдж

---

##### `updateBadge(Badge $badge, array $data): Badge`
**Параметры:**
- `Badge $badge` - бейдж
- `array $data` - данные

**Что внутри:**
- Обработать обновление иконки (удалить старую, загрузить новую)
- Обновить бейдж
- Вернуть обновленный бейдж

---

##### `deleteBadge(Badge $badge): bool`
**Параметры:**
- `Badge $badge` - бейдж

**Что внутри:**
- Удалить иконку через FileService
- Удалить бейдж
- Вернуть true

---

##### `getStudentBadges(User $user): Collection`
**Параметры:**
- `User $user` - студент

**Что внутри:**
- Получить бейджи студента через pivot
- Вернуть коллекцию

---

#### `SimulatorService.php`

##### `createSimulator(array $data): Simulator`
**Параметры:**
- `array $data` - данные симулятора

**Что внутри:**
- Обработать загрузку preview_image
- Создать симулятор
- Вернуть созданный симулятор

---

##### `updateSimulator(Simulator $simulator, array $data): Simulator`
**Параметры:**
- `Simulator $simulator` - симулятор
- `array $data` - данные

**Что внутри:**
- Обработать обновление preview_image
- Обновить симулятор
- Вернуть обновленный симулятор

---

##### `deleteSimulator(Simulator $simulator): bool`
**Параметры:**
- `Simulator $simulator` - симулятор

**Что внутри:**
- Проверить наличие активных сессий
- Удалить preview_image
- Удалить симулятор
- Вернуть true

---

##### `startSession(User $user, Simulator $simulator): SimulatorSession`
**Параметры:**
- `User $user` - студент
- `Simulator $simulator` - симулятор

**Что внутри:**
- Проверить, нет ли активной сессии
- Создать новую сессию SimulatorSession
- Установить started_at
- Вернуть сессию

---

##### `hasActiveSession(User $user, Simulator $simulator): bool`
**Параметры:**
- `User $user` - студент
- `Simulator $simulator` - симулятор

**Что внутри:**
- Проверить наличие активной (не завершенной) сессии
- Вернуть true/false

---

##### `completeSession(SimulatorSession $session, array $data): SimulatorSession`
**Параметры:**
- `SimulatorSession $session` - сессия
- `array $data` - данные (score, time_spent, answers)

**Что внутри:**
- Обновить сессию (score, time_spent, completed_at)
- Начислить очки по навыкам на основе score
- Обновить прогресс студента
- Вернуть обновленную сессию

---

#### `NotificationService.php`

##### `notifyPartnerAboutApplication(CaseApplication $application): void`
**Параметры:**
- `CaseApplication $application` - заявка

**Что внутри:**
- Получить партнера из кейса
- Создать уведомление (AppNotification) для партнера
- Сохранить уведомление

---

##### `notifyTeamAboutApproval(CaseApplication $application): void`
**Параметры:**
- `CaseApplication $application` - заявка

**Что внутри:**
- Получить всех участников команды
- Создать уведомления для каждого участника
- Сохранить уведомления

---

##### `notifyStudentsAboutNewCase(CaseModel $case): void`
**Параметры:**
- `CaseModel $case` - кейс

**Что внутри:**
- Получить студентов, у которых есть навыки, соответствующие required_skills кейса
- Создать уведомления для этих студентов
- Сохранить уведомления

---

##### `notifyApplicationRejection(CaseApplication $application): void`
**Параметры:**
- `CaseApplication $application` - отклоненная заявка

**Что внутри:**
- Получить лидера команды из application->leader_id
- Создать уведомление (AppNotification) для лидера команды
- Установить тип уведомления 'application_rejected'
- Сохранить уведомление с информацией о причине отклонения (если есть)

---

#### `FileService.php`

##### `storeAvatar(UploadedFile $file): string`
**Параметры:**
- `UploadedFile $file` - файл аватара

**Что внутри:**
- Валидировать файл (размер, тип)
- Сгенерировать уникальное имя
- Сохранить в storage/app/public/avatars
- Вернуть путь к файлу

---

##### `storeLogo(UploadedFile $file): string`
**Параметры:**
- `UploadedFile $file` - файл логотипа

**Что внутри:**
- Валидировать файл
- Сгенерировать уникальное имя
- Сохранить в storage/app/public/logos
- Вернуть путь к файлу

---

##### `storeBadgeIcon(UploadedFile $file): string`
**Параметры:**
- `UploadedFile $file` - файл иконки

**Что внутри:**
- Валидировать файл
- Сгенерировать уникальное имя
- Сохранить в storage/app/public/badge-icons
- Вернуть путь к файлу

---

##### `deleteFile(string $path): bool`
**Параметры:**
- `string $path` - путь к файлу

**Что внутри:**
- Удалить файл из storage
- Вернуть true/false

---

#### `DashboardService.php`

##### `getStatistics(): array`
**Что внутри:**
- Подсчитать общее количество студентов
- Подсчитать общее количество партнеров
- Подсчитать активные кейсы
- Подсчитать завершенные кейсы за период
- Подсчитать новые регистрации за период
- Вернуть массив статистики

---

#### `ProgressLogService.php`

##### `logSimulatorCompletion(SimulatorSession $session): void`
**Параметры:**
- `SimulatorSession $session` - сессия

**Что внутри:**
- Рассчитать очки на основе score
- Определить навыки, связанные с симулятором (если есть)
- Обновить UserSkill для студента
- Создать запись ProgressLog
- Проверить, не получен ли новый бейдж через BadgeService::checkBadgeEligibility()

---

##### `getSkillProgressHistory(User $user, ?Skill $skill = null): Collection`
**Параметры:**
- `User $user` - студент
- `?Skill $skill` - навык (опционально, если null - все навыки)

**Что внутри:**
- Получить записи ProgressLog для студента
- Фильтровать по навыку (если указан)
- Отсортировать по дате
- Вернуть коллекцию

---

#### `AnalyticsService.php`

##### `getPartnerAnalytics(Partner $partner, array $filters): array`
**Параметры:**
- `Partner $partner` - партнер
- `array $filters` - фильтры (date_from, date_to, case_id)

**Что внутри:**
- Получить кейсы партнера с фильтрами
- Подсчитать статистику по заявкам (конверсия, среднее время)
- Подсчитать статистику по командам
- Построить данные для графиков (динамика, популярность)
- Вернуть массив аналитики

---

#### `BadgeService.php` (дополнительные методы)

##### `checkBadgeEligibility(User $user): array`
**Параметры:**
- `User $user` - студент

**Что внутри:**
- Получить общее количество очков студента
- Найти бейджи, которые еще не получены, но требуемые очки достигнуты
- Начислить эти бейджи студенту
- Вернуть массив полученных бейджей

---

## 📝 Примечания к реализации

1. **Транзакции:** Использовать DB::transaction() для операций, изменяющих несколько таблиц
2. **Eager Loading:** Использовать with() для загрузки связей, чтобы избежать N+1 проблем
3. **Пагинация:** Использовать paginate() для списков
4. **Валидация прав:** Проверять права доступа в контроллерах перед вызовом сервисов
5. **Уведомления:** Создавать через NotificationService, не напрямую
6. **Файлы:** Все операции с файлами через FileService
7. **Ошибки:** Выбрасывать исключения при нарушении бизнес-правил
8. **Кеширование:** Для статистики и аналитики использовать кеширование (если нужно)

---

**Создано:** 2025-11-02  
**Версия:** 1.0

