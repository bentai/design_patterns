<?php

namespace RefactoringGuru\ChainOfResponsibility\RealWorld;

/**
 * 经典的CoR模式为组成链（即Handler）的对象声明单个角色。
 * 在我们的示例中，让我们区分中间件和最终应用程序的处理程序，该处理程序在请求通过所有中间件对象时执行
 * The classic CoR pattern declares a single role for objects that make up a
 * chain, which is a Handler. In our example, let's differentiate between
 * middleware and a final application's handler, which is executed when a
 * request gets through all the middleware objects.
 *
 * The base Middleware class declares an interface for linking middleware
 * objects into a chain.
 * 中间件基础类声明了一个接口，用于将中间件对象链接到链中
 */
abstract class Middleware
{
    /**
     * @var Middleware
     */
    private $next;

    /**
     * 此方法可用于构建中间件对象链
     * This method can be used to build a chain of middleware objects.
     */
    public function linkWith(Middleware $next): Middleware
    {
        $this->next = $next;

        return $next;
    }

    /**
     * 子类必须重写此方法以提供自己的检查。如果子类无法处理请求，则它可以退回到父级实现。
     * Subclasses must override this method to provide their own checks. A
     * subclass can fall back to the parent implementation if it can't process a
     * request.
     */
    public function check(string $email, string $password): bool
    {
        if (!$this->next) {
            return true;
        }

        return $this->next->check($email, $password);
    }
}

/**
 * 此具体中间件检查具有给定凭据的用户是否存在
 * This Concrete Middleware checks whether a user with given credentials exists.
 */
// 用户存在中间件
class UserExistsMiddleware extends Middleware
{
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function check(string $email, string $password): bool
    {
        if (!$this->server->hasEmail($email)) {
            // UserExistsMiddleware：此电子邮件未注册！
            echo "UserExistsMiddleware: This email is not registered!\n";

            return false;
        }

        if (!$this->server->isValidPassword($email, $password)) {
            // UserExistsMiddleware：错误的密码！
            echo "UserExistsMiddleware: Wrong password!\n";

            return false;
        }

        return parent::check($email, $password);
    }
}

/**
 * 该具体中间件检查与请求相关联的用户是否具有足够的权限
 * This Concrete Middleware checks whether a user associated with the request
 * has sufficient permissions.
 */
// 角色检查中间件
class RoleCheckMiddleware extends Middleware
{
    public function check(string $email, string $password): bool
    {
        if ($email === "admin@example.com") {
            echo "RoleCheckMiddleware: Hello, admin!\n";

            return true;
        }
        echo "RoleCheckMiddleware: Hello, user!\n";

        return parent::check($email, $password);
    }
}

/**
 * // 此具体中间件检查是否有太多失败的登录请求
 * This Concrete Middleware checks whether there are too many failed login
 * requests.
 */
// 节流中间件
class ThrottlingMiddleware extends Middleware
{
    // 返回每分钟
    private $requestPerMinute;

    private $request;

    // 当前时间
    private $currentTime;

    public function __construct(int $requestPerMinute)
    {
        $this->requestPerMinute = $requestPerMinute;
        $this->currentTime = time();
    }

    /**
     * 请注意，可以在此方法的开头和结尾处插入parent :: check调用
     * Please, note that the parent::check call can be inserted both at the
     * beginning of this method and at the end.
     *
     * 与对所有中间件对象进行简单循环相比，这提供了更大的灵活性。
     * 例如，中间件可以通过在所有其他组件之后运行其检查来更改检查的顺序
     * This gives much more flexibility than a simple loop over all middleware
     * objects. For instance, a middleware can change the order of checks by
     * running its check after all the others.
     */
    public function check(string $email, string $password): bool
    {
        if (time() > $this->currentTime + 60) {
            $this->request = 0;
            $this->currentTime = time();
        }

        $this->request++;

        if ($this->request > $this->requestPerMinute) {
            // ThrottlingMiddleware：超出请求限制！
            echo "ThrottlingMiddleware: Request limit exceeded!\n";
            die();
        }

        return parent::check($email, $password);
    }
}

/**
 * 这是一个充当真实处理程序的应用程序类。 Server类在启动与请求关联的一些业务逻辑之前，使用CoR模式执行一组各种身份验证中间件
 * This is an application's class that acts as a real handler. The Server class
 * uses the CoR pattern to execute a set of various authentication middleware
 * before launching some business logic associated with a request.
 */
class Server
{
    private $users = [];

    /**
     * @var Middleware
     */
    private $middleware;

    /**
     * 客户端可以使用一系列中间件对象配置服务器
     * The client can configure the server with a chain of middleware objects.
     */
    public function setMiddleware(Middleware $middleware): void
    {
        $this->middleware = $middleware;
    }

    /**
     * 服务器从客户端获取电子邮件和密码，然后将授权请求发送到中间件
     * The server gets the email and password from the client and sends the
     * authorization request to the middleware.
     */
    public function logIn(string $email, string $password): bool
    {
        if ($this->middleware->check($email, $password)) {
            // 服务器：授权已成功！
            echo "Server: Authorization has been successful!\n";

            // 做一些对授权用户有用的事情
            // Do something useful for authorized users.

            return true;
        }

        return false;
    }

    public function register(string $email, string $password): void
    {
        $this->users[$email] = $password;
    }

    public function hasEmail(string $email): bool
    {
        return isset($this->users[$email]);
    }

    public function isValidPassword(string $email, string $password): bool
    {
        return $this->users[$email] === $password;
    }
}

/**
 * The client code.
 */
$server = new Server();
$server->register("admin@example.com", "admin_pass");
$server->register("user@example.com", "user_pass");

// 所有中间件均已链接。客户端可以构建各种配置
// All middleware are chained. The client can build various configurations of
// 链取决于其需求
// chains depending on its needs.

$middleware = new ThrottlingMiddleware(2);
var_dump($middleware);
$middleware
    ->linkWith(new UserExistsMiddleware($server))
    ->linkWith(new RoleCheckMiddleware);
var_dump($middleware);
exit;

/// 服务器从客户端代码获取链
// The server gets a chain from the client code.
$server->setMiddleware($middleware);

// ...

//do {
//    echo "\nEnter your email:\n";
//    $email = readline();
//    echo "Enter your password:\n";
//    $password = readline();
//    $success = $server->logIn($email, $password);
//} while (!$success);
//Enter your email:
//asd
//Enter your password:
//123
//UserExistsMiddleware: This email is not registered!
//UserExistsMiddleware: This email is not registered!

//Enter your email:
//admin@example.com
//Enter your password:
//wrong
//UserExistsMiddleware: Wrong password!
//
//Enter your email:
//admin@example.com
//Enter your password:
//letmein
//ThrottlingMiddleware: Request limit exceeded!
//
//
//
//Enter your email:
//admin@example.com
//Enter your password:
//admin_pass
//RoleCheckMiddleware: Hello, admin!
//Server: Authorization has been successful!