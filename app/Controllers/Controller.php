<?php
class Controller {
    protected array $config = [
        'app_name' => 'Selling Shop',
        'items_per_page' => 10,
    ];

    protected function render(string $view, array $data = []): void {
        extract($data);
        $flash = getFlash();
        $viewFile = base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
        include base_path('app/Views/partials/header.php');
        include $viewFile;
        include base_path('app/Views/partials/footer.php');
    }

    protected function redirect(string $path): void {
        redirect($path);
    }

    protected function currentUser(): ?array {
        return currentUser();
    }

    protected function requireLogin(): void {
        requireLogin();
    }

    protected function requireRole(array $roles): void {
        requireRole($roles);
    }

    protected function pagination(int $total, int $page, int $perPage = 10): array {
        $pages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        return ['page' => $page, 'pages' => $pages, 'perPage' => $perPage];
    }
}
