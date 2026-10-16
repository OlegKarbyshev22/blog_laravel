<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@blog.test'], [
            'name' => 'Администратор', 'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        $reader = User::updateOrCreate(['email' => 'user@blog.test'], [
            'name' => 'Анна Читатель', 'password' => Hash::make('password'), 'role' => 'user',
        ]);

        $tech = Category::updateOrCreate(['slug' => 'technologies'], ['name' => 'Технологии', 'description' => 'Разработка, сервисы и цифровой мир']);
        $travel = Category::updateOrCreate(['slug' => 'travel'], ['name' => 'Путешествия', 'description' => 'Маршруты, города и открытия']);
        $culture = Category::updateOrCreate(['slug' => 'culture'], ['name' => 'Культура', 'description' => 'Книги, кино, музыка и люди']);

        $posts = [
            [$tech, 'Как небольшие привычки меняют работу разработчика', 'small-habits-for-developers', 'Пять простых практик, которые помогают писать код спокойнее и качественнее.', "Хороший результат редко рождается из одного героического рывка. Обычно он складывается из небольших решений, которые мы повторяем каждый день.\n\nНачните с короткого плана перед работой. Запишите, что именно должно измениться и как вы поймёте, что задача решена. Это занимает две минуты, но заметно уменьшает количество лишних движений.\n\nВторая привычка — оставлять рабочее место чуть лучше, чем оно было. Переименовать непонятную переменную, добавить тест на найденную ошибку, уточнить инструкцию запуска. Такие улучшения постепенно превращаются в устойчивую культуру разработки.\n\nНаконец, регулярно показывайте результат другим. Короткая обратная связь на раннем этапе дешевле идеальной реализации неверной идеи.", 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80'],
            [$travel, 'Выходные в незнакомом городе без спешки', 'weekend-without-rush', 'Как составить маршрут, в котором останется место для случайных открытий.', "Путешествие не обязано быть соревнованием по количеству достопримечательностей. Иногда лучший способ узнать город — выбрать один район и провести в нём целый день.\n\nОтметьте на карте рынок, небольшой музей и парк. Между ними оставьте свободное время: зайдите в книжный магазин, сверните в тихий переулок, поговорите с владельцем кафе. Именно такие эпизоды чаще всего становятся главными воспоминаниями.\n\nПлан нужен не как строгий график, а как опора. Он снимает тревогу и одновременно позволяет менять направление, когда впереди появляется что-то интересное.", 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80'],
            [$culture, 'Почему мы возвращаемся к любимым книгам', 'why-we-reread-books', 'Повторное чтение — это встреча не только с текстом, но и с прежней версией себя.', "При первом чтении мы следим за сюжетом и хотим узнать, что произойдёт дальше. При повторном эта спешка исчезает. Становятся заметны детали, ритм фраз и связи, которые раньше проходили мимо.\n\nНо меняется не только наше внимание. Между двумя встречами с книгой проходит жизнь: появляется новый опыт, меняются вопросы и ожидания. Знакомый текст отвечает уже другому читателю.\n\nПоэтому перечитывание не означает движение назад. Это способ измерить пройденное расстояние и увидеть, какие мысли остались с нами, а какие приобрели совершенно новый смысл.", 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=1200&q=80'],
        ];

        foreach ($posts as [$category, $title, $slug, $excerpt, $body, $image]) {
            Post::updateOrCreate(['slug' => $slug], [
                'user_id' => $admin->id, 'category_id' => $category->id, 'title' => $title,
                'excerpt' => $excerpt, 'body' => $body, 'image_url' => $image,
                'status' => 'published', 'published_at' => now()->subDays(random_int(1, 15)),
            ]);
        }

        $firstPost = Post::where('slug', 'small-habits-for-developers')->firstOrFail();
        Comment::updateOrCreate(['post_id' => $firstPost->id, 'user_id' => $reader->id, 'body' => 'Особенно полезен совет про раннюю обратную связь. Спасибо!'], [
            'status' => 'approved', 'moderated_by' => $admin->id, 'moderated_at' => now(),
        ]);
        Rating::updateOrCreate(['post_id' => $firstPost->id, 'user_id' => $reader->id], ['value' => 5]);
        Enrollment::updateOrCreate(
            ['post_id' => $firstPost->id, 'user_id' => $reader->id],
            ['status' => 'confirmed'],
        );
    }
}
