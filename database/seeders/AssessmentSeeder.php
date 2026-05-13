<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assessment;

class AssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slug = 'l1-sample-5q';

        $assessment = Assessment::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => 'Sample: Dasar Class & Object (5 soal)',
                'description' => 'Assessment singkat 5 soal untuk konsep dasar class dan object',
                'time_limit' => 15,
            ]
        );

        $questions = [
            [
                'text' => 'Apa itu class dalam OOP?',
                'options' => [
                    ['label' => 'a', 'text' => 'Blueprint untuk membuat object', 'is_correct' => true],
                    ['label' => 'b', 'text' => 'Tipe data primitif', 'is_correct' => false],
                    ['label' => 'c', 'text' => 'Library eksternal', 'is_correct' => false],
                    ['label' => 'd', 'text' => 'Fungsi global', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Apa yang dimaksud instance?',
                'options' => [
                    ['label' => 'a', 'text' => 'Template class', 'is_correct' => false],
                    ['label' => 'b', 'text' => 'Objek yang dibuat dari class', 'is_correct' => true],
                    ['label' => 'c', 'text' => 'Method dalam class', 'is_correct' => false],
                    ['label' => 'd', 'text' => 'Variabel global', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Apa fungsi constructor?',
                'options' => [
                    ['label' => 'a', 'text' => 'Menghapus object', 'is_correct' => false],
                    ['label' => 'b', 'text' => 'Menginisialisasi object saat dibuat', 'is_correct' => true],
                    ['label' => 'c', 'text' => 'Menjalankan garbage collector', 'is_correct' => false],
                    ['label' => 'd', 'text' => 'Menentukan tipe data', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Metode atau fungsi dalam class disebut?',
                'options' => [
                    ['label' => 'a', 'text' => 'Property', 'is_correct' => false],
                    ['label' => 'b', 'text' => 'Method', 'is_correct' => true],
                    ['label' => 'c', 'text' => 'Module', 'is_correct' => false],
                    ['label' => 'd', 'text' => 'Package', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Apa tujuan encapsulation?',
                'options' => [
                    ['label' => 'a', 'text' => 'Meningkatkan performa', 'is_correct' => false],
                    ['label' => 'b', 'text' => 'Menyembunyikan detail implementasi dan melindungi data', 'is_correct' => true],
                    ['label' => 'c', 'text' => 'Mengurangi ukuran file', 'is_correct' => false],
                    ['label' => 'd', 'text' => 'Membuat semua method static', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $question = $assessment->questions()->updateOrCreate(
                ['text' => $q['text']],
                ['text' => $q['text']]
            );

            foreach ($q['options'] as $opt) {
                $question->options()->updateOrCreate(
                    ['label' => $opt['label']],
                    [
                        'label' => $opt['label'],
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ]
                );
            }
        }
    }
}
