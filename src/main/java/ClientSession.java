import java.io.*;
import java.net.Socket;
import java.nio.charset.Charset;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;
import java.util.TimeZone;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by step on 30.03.14.
 */
public class ClientSession implements Runnable {
    @Override public void run() {
        try { /* Получаем заголовок сообщения от клиента */
            String header = readHeader();
            System.out.println(header + "\n");
            String reqType = getRequestType(header); /* Получаем из заголовка тип запроса*/
            //System.out.println(reqType + "\n");
            String url = getURIFromHeader(header); /* Получаем из заголовка урл */
            //System.out.println("Resource: " + url + "\n"); /* Отправляем содержимое по урлу клиенту */
            int code = sendToClient(url, reqType);
            //System.out.println("Result code: " + code + "\n");
        }
        catch (IOException e) {
            e.printStackTrace();
        }
        finally {
            try {
                socket.close();
            }
            catch (IOException e) {
                e.printStackTrace();
            }
        }
    }

    public ClientSession(Socket socket) throws IOException {
        this.socket = socket;
        initialize();
    }

    private void initialize() throws IOException {
        in = socket.getInputStream();    /* Получаем поток ввода, в который помещаются сообщения от клиента */
        out = socket.getOutputStream();  /* Получаем поток вывода, для отправки сообщений клиенту */
    }


    /* Считывает заголовок сообщения от клиента. Вернём строку с заголовком сообщения от клиента. */
    private String readHeader() throws IOException {
        BufferedReader reader = new BufferedReader(new InputStreamReader(in));  /* используем буфферридер, чтобы проще было обращаться с потоком ввода-вывода */
        StringBuilder builder = new StringBuilder();  //String модифицирую файл с текстом создавал бы новые файлы, а StringBuilder изменяет существующие
        String ln = null;
        while (true) {
            ln = reader.readLine();
            if (ln == null || ln.isEmpty()) {
                break;
            }
            builder.append(ln + System.getProperty("line.separator"));
        }
        return builder.toString();
    }

    /* Вытаскиваем урл из заголовка сообщения от клиента. Если просто localhost, то index.html отдаём.
       Отрезаем параметры, если они вдруг передаются. Возвращает урл */
    private String getURIFromHeader(String header) throws UnsupportedEncodingException {
        int from = header.indexOf(" ") + 1;   //url находится между первым и вторым пробелом в загаловках, e.x. GET vk.com HTTP..
        int to = header.indexOf("H", from);
        if (to == -1) {
            String sep = System.getProperty("line.separator");
            to = header.indexOf(sep);
        } else {
            to--;
        }
        String uri = null;
        if (to-from>1) {
            uri = header.substring(from, to);
        } else {
            uri = "/index.html";
        }
        uri = java.net.URLDecoder.decode(uri, "UTF-8");
        int paramIndex = uri.indexOf("?");      //отрезаем параметры
        if (paramIndex != -1) {
            uri = uri.substring(0, paramIndex);
        }
        return DEFAULT_FILES_DIR + uri;   //корневой директорией сервера считается папка www
    }

    /* Отправляем ответ клиенту
      * Проверяем корректность урла
      * Выбираем код запроса
      * Формируем заголовки ответа
      * Пишем ответ*/
    private int sendToClient(String url, String reqType) throws IOException {
        File expectFile = new File(url);
        int code = 0;
        /* Проверка уязвимости */
        String dirPath = expectFile.getParentFile().getCanonicalPath();  //путь к файлу без всего лишнего
        int urlCheck = dirPath.indexOf(DEFAULT_FILES_DIR);
        //System.out.println(dirPath);

        int cLength = (int)expectFile.length();
        InputStream strm = null;
        boolean exist = expectFile.exists();
        boolean get = reqType.equals("GET");
        if (get | reqType.equals("HEAD")) {
            if (exist & !expectFile.isFile()) {     //если папка
                url = url + "/index.html";
                File index = new File(url);
                cLength = (int)index.length();
                exist = index.exists();
                if (!exist) {
                    code = 403;
                }
            }
            if (exist & urlCheck!=-1) {
                strm = new FileInputStream(url);
                code = 200;
            } else if (code == 0){
                code = 404;
                cLength = 0;        //если не 0, то будет ошибка
            }
        } else {
            code = 405;
            cLength = 0;
        }
        String fileType = getContentType(url);
        String header = getHeader(code, cLength, fileType);
        System.out.println(header);
        PrintStream answer = new PrintStream(out, true, "UTF-8");
        answer.print(header);
        if (code == 200) {
            if (get) {
                int count = 0;
                byte[] buffer = new byte[10024];  //пусть массив будет из 1024 байт состоять
                while((count = strm.read(buffer)) != -1) {  //пытается прочесть максимум b.length байтов из входного потока в массив b. Возвращаем в count количество байтов, в действительности прочитанных из потока;
                    out.write(buffer, 0, count);   //записывает в поток из буффера count байт, начиная с элемента b[0];
                }
            }
            strm.close();
        }
        return code;
    }

    /* Возвращаем http заголовок ответа со всеми пунктами */
    private String getHeader(int code, long length, String type) {
        String date = getCorrectDate();
        StringBuffer buffer = new StringBuffer();
        buffer.append("HTTP/1.0 " + code + " " + getAnswer(code) + "\r\n");
        buffer.append("Date: " + date + "\r\n");
        buffer.append("Server: Steve/2.0\r\n");
        buffer.append("Content-Length: " + length + "\r\n");
        buffer.append("Content-Type: " + type + "\r\n");
        buffer.append("Connection: close" + "\r\n");
        buffer.append("\r\n");
        return buffer.toString(); }

    /* Получаем тип запроса (GET/POST/..) */
    private  String getRequestType(String head) {
        int to = head.indexOf(" ");
        String reqType = head.substring(0, to);
        return reqType;
    }

    /* Состовляем корректный Content-type*/
    private String getContentType(String url) {
        int index = 0;
        String fileType = "unknown";
        Pattern forType = Pattern.compile("\\.[a-zA-Z]+$");  /* создаем шаблон*/
        Matcher fType = forType.matcher(url);           /* применяем шаблон для строки нашей */
        while (fType.find()) {      /* находим последние цифры*/
            index = fType.start();
        }
        if (index !=0 ) {
            fileType = url.substring(index+1);
        }
        switch (fileType) {
            case "html":
            case "css":
                fileType = "text/" + fileType;
                break;
            case "js":
                fileType = "application/javascript";
                break;
            case "jpg":
                fileType = "image/jpeg";
                break;
            case "jpeg":
            case "png":
            case "gif":
                fileType = "image/" + fileType;
                break;
            case  "swf":
                fileType = "application/x-shockwave-flash";
                break;
            default:
                fileType = "text/plain";
        }
        return fileType;
    }

    /* Состовляем корректную дату/время */
    private String getCorrectDate() {
        SimpleDateFormat sd = new SimpleDateFormat("dd MMM yyyy HH:mm:ss", Locale.ENGLISH);
        sd.setTimeZone(TimeZone.getTimeZone("GMT"));
        String rightDate = sd.format(new Date());
        String date = new Date().toString();
        int pars = date.indexOf(" ");
        String dayOfWeek = date.substring(0, pars);
        String finalDateHeader = dayOfWeek + ", " + rightDate + " GMT";
        //System.out.println(finalDateHeader);
        return finalDateHeader;
    }

    /* Возвращаем комментарий к коду результата отправки.  */
    private String getAnswer(int code) {
        switch (code) {
            case 200:
                return "OK";
            case 403:
                return "Forbidden";
            case 404:
                return "Not Found";
            case 405:
                return  "Method Not Allowed";
            default:
                return "Internal Server Error";
        }
    }
    private Socket socket;
    private InputStream in = null;
    private OutputStream out = null;
    private static final String DEFAULT_FILES_DIR = "/home/step/Technopark_3_sem/Highload/JavaHttpServer/www";
}