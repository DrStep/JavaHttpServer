import java.io.DataInputStream;
import java.io.IOException;
import java.io.PrintStream;
import java.net.ServerSocket;
import java.net.Socket;

/**
 * Created by step on 30.03.14.
 */
public class Server {
    /** * Первым аргументом может идти номер порта. */
    public static void main(String[] args) { /* Если аргументы отсутствуют, порт принимает значение поумолчанию */
        int port = DEFAULT_PORT;
        if (args.length > 0) {
            port = Integer.parseInt(args[0]);
        } /* Создаем серверный сокет на полученном порту */
        ServerSocket serverSocket = null;
        try {
            serverSocket = new ServerSocket(port);
            System.out.println("Server started on port: " + serverSocket.getLocalPort() + "\n");
        }
        catch (IOException e) {
            System.out.println("Port " + port + " is blocked.");
            System.exit(-1);
        } /* * Если порт был свободен и сокет был успешно создан, можно переходить к * следующему шагу - ожиданию клинтов */
        while (true) {
            try {
                Socket clientSocket = serverSocket.accept();  //ждём, пока кто-то подключится к серверу (зайдёт на порт)
                ClientSession session = new ClientSession(clientSocket); /* Для обработки запроса от каждого клиента создается * отдельный объект и отдельный поток */
                new Thread(session).start();
            }
            catch (IOException e) {
                System.out.println("Failed to establish connection.");
                System.out.println(e.getMessage());
                System.exit(-1);
            }
        }
    }
    private static final int DEFAULT_PORT = 8080;
}

//nginx -s reload;  apachectl -k stop /     apachectl -k graceful - restart