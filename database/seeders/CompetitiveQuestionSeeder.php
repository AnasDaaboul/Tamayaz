<?php

namespace Database\Seeders;

use App\Models\CompetitiveQuestion;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CompetitiveQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active units to distribute questions among them
        $units = \App\Models\Unit::where('is_active', true)->get();
        
        if ($units->isEmpty()) {
            $this->command->info('No active units found. Please create units first.');
            return;
        }
        
        // Question categories and templates to generate diverse content
        $categories = [
            'Programming' => [
                'What is the correct way to declare a variable in %s?',
                'Which data structure is most efficient for %s?',
                'What is the time complexity of %s algorithm?',
                'Which design pattern is best suited for %s?',
                'How do you implement %s in this language?'
            ],
            'Mathematics' => [
                'Calculate the derivative of %s',
                'Solve the equation: %s',
                'What is the integral of %s?',
                'Find the value of %s when x = 5',
                'What is the probability of %s occurring?'
            ],
            'Science' => [
                'What is the scientific explanation for %s?',
                'Which element in the periodic table %s?',
                'Explain the process of %s',
                'What is the relationship between %s and %s?',
                'How does %s affect the environment?'
            ],
            'History' => [
                'When did %s occur?',
                'Who was responsible for %s?',
                'What were the consequences of %s?',
                'Explain the significance of %s',
                'How did %s influence modern society?'
            ],
        ];
        
        // Variables to fill in the question templates
        $variables = [
            'Programming' => [
                'JavaScript', 'Python', 'Java', 'C++', 'PHP',
                'searching large datasets', 'frequent insertions and deletions', 'maintaining sorted data',
                'quick sort', 'merge sort', 'bubble sort', 'heap sort',
                'factory', 'singleton', 'observer', 'strategy',
                'authentication', 'file handling', 'database connections', 'API requests'
            ],
            'Mathematics' => [
                'f(x) = x² + 3x - 5', 'f(x) = sin(x) + cos(x)', 'f(x) = e^x',
                '3x + 7 = 22', 'x² - 4x + 4 = 0', '2x - 5y = 10',
                'x³ + 2x', 'sin(2x)', 'ln(x)/x', 'e^(-x²)',
                'x² + 3x - 2', '2^n - 1', 'n!/(n-k)!',
                'getting two consecutive sixes when rolling a die', 'selecting a red card from a standard deck'
            ],
            'Science' => [
                'gravity', 'photosynthesis', 'nuclear fusion', 'black holes',
                'has the highest electronegativity', 'is most reactive with water', 'has the lowest melting point',
                'cellular respiration', 'DNA replication', 'protein synthesis',
                'temperature', 'pressure', 'velocity', 'acceleration',
                'carbon emissions', 'deforestation', 'plastic pollution'
            ],
            'History' => [
                'World War II', 'the Industrial Revolution', 'the French Revolution',
                'the Treaty of Versailles', 'the American Civil War', 'the Cold War',
                'economic depression', 'political instability', 'technological advancement',
                'the Magna Carta', 'the Declaration of Independence', 'the fall of the Berlin Wall',
                'colonialism', 'the Renaissance', 'the Digital Revolution'
            ],
        ];
        
        // Difficulty distribution
        $difficulties = ['easy', 'medium', 'hard'];
        $difficultyWeights = [30, 50, 20]; // 30% easy, 50% medium, 20% hard
        
        $questionsCreated = 0;
        $totalToCreate = 100;
        
        while ($questionsCreated < $totalToCreate) {
            // Select random unit
            $unit = $units->random();
            
            // Select random category
            $category = array_rand($categories);
            $questionTemplates = $categories[$category];
            $categoryVariables = $variables[$category];
            
            // Select random question template
            $questionTemplate = $questionTemplates[array_rand($questionTemplates)];
            
            // Generate question text
            $questionText = $questionTemplate;
            
            // Replace %s placeholders with random variables
            while (strpos($questionText, '%s') !== false) {
                $randomVariable = $categoryVariables[array_rand($categoryVariables)];
                $questionText = preg_replace('/\%s/', $randomVariable, $questionText, 1);
            }
            
            // Generate 4 options (one correct, three incorrect)
            $correctOption = $this->generateOption($category, true);
            $options = [$correctOption];
            
            // Generate 3 incorrect options
            for ($i = 0; $i < 3; $i++) {
                $incorrectOption = $this->generateOption($category, false);
                // Ensure no duplicate options
                while (in_array($incorrectOption, $options)) {
                    $incorrectOption = $this->generateOption($category, false);
                }
                $options[] = $incorrectOption;
            }
            
            // Shuffle options
            shuffle($options);
            
            // Determine difficulty based on weights
            $rand = mt_rand(1, 100);
            $difficultyIndex = 0;
            $cumulativeWeight = 0;
            
            foreach ($difficultyWeights as $index => $weight) {
                $cumulativeWeight += $weight;
                if ($rand <= $cumulativeWeight) {
                    $difficultyIndex = $index;
                    break;
                }
            }
            
            $difficulty = $difficulties[$difficultyIndex];
            
            // Create the question
            CompetitiveQuestion::create([
                'unit_id' => $unit->id,
                'question_text' => $questionText,
                'options' => $options,
                'correct_answer' => $correctOption,
                'difficulty' => $difficulty,
                'is_active' => true,
            ]);
            
            $questionsCreated++;
        }
        
        $this->command->info("Created {$totalToCreate} competitive questions.");
    }
    
    /**
     * Generate a random option based on category
     *
     * @param string $category
     * @param bool $isCorrect
     * @return string
     */
    private function generateOption(string $category, bool $isCorrect): string
    {
        $options = [
            'Programming' => [
                // Correct answers for programming questions
                'correct' => [
                    'Using the appropriate declaration keyword (var, let, const)',
                    'O(log n) for binary search operations',
                    'Hash tables for constant-time lookups',
                    'Factory pattern for object creation',
                    'Dependency injection for loose coupling',
                    'Using try-catch blocks for error handling',
                    'Implementing interfaces for polymorphism',
                    'Using async/await for asynchronous operations',
                    'Applying SOLID principles',
                    'Using proper authentication middleware'
                ],
                // Incorrect answers for programming questions
                'incorrect' => [
                    'Using print statements for debugging in production',
                    'Storing passwords in plain text',
                    'O(n²) for all sorting algorithms',
                    'Using global variables for all data',
                    'Ignoring exception handling',
                    'Hardcoding credentials in source code',
                    'Using nested loops for all operations',
                    'Implementing business logic in the view layer',
                    'Avoiding code comments entirely',
                    'Using the same variable name for different purposes'
                ]
            ],
            'Mathematics' => [
                // Correct answers for math questions
                'correct' => [
                    '2x + 3',
                    'cos(x) - sin(x)',
                    'e^x',
                    'x = 5',
                    '1/6',
                    'The derivative of x² is 2x',
                    'The integral of 2x is x² + C',
                    'The solution is x = 2',
                    'The limit approaches infinity',
                    'The probability is 0.25'
                ],
                // Incorrect answers for math questions
                'incorrect' => [
                    '3x + 2',
                    'sin(x) + cos(x)',
                    'ln(x)',
                    'x = 4',
                    '1/4',
                    'The derivative of x² is x',
                    'The integral of 2x is 2x² + C',
                    'The solution is x = 3',
                    'The limit approaches zero',
                    'The probability is 0.5'
                ]
            ],
            'Science' => [
                // Correct answers for science questions
                'correct' => [
                    'The conversion of light energy into chemical energy',
                    'The process of combining atomic nuclei to form heavier nuclei',
                    'Regions of spacetime where gravity is so strong that nothing can escape',
                    'The transfer of genetic information from DNA to RNA to protein',
                    'The breakdown of glucose to release energy',
                    'The periodic arrangement of elements based on atomic number',
                    'The conservation of energy in a closed system',
                    'The acceleration due to gravity on Earth is approximately 9.8 m/s²',
                    'Water expands when it freezes',
                    'The human body has 206 bones'
                ],
                // Incorrect answers for science questions
                'incorrect' => [
                    'The conversion of chemical energy into light energy',
                    'The process of splitting atomic nuclei into lighter nuclei',
                    'Areas where magnetic fields are strongest',
                    'The transfer of proteins to create DNA',
                    'The synthesis of glucose from carbon dioxide',
                    'The random arrangement of elements based on discovery date',
                    'The creation of energy in an open system',
                    'The acceleration due to gravity on Earth is approximately 5.2 m/s²',
                    'Water contracts when it freezes',
                    'The human body has 150 bones'
                ]
            ],
            'History' => [
                // Correct answers for history questions
                'correct' => [
                    'From 1939 to 1945',
                    'The signing occurred on June 28, 1919',
                    'It led to significant political and social changes',
                    'It established fundamental rights and liberties',
                    'It resulted in the formation of new nation-states',
                    'It was a period of cultural and intellectual growth',
                    'It marked the end of colonial rule in many regions',
                    'It was characterized by rapid technological advancement',
                    'It influenced modern democratic principles',
                    'It changed global economic structures'
                ],
                // Incorrect answers for history questions
                'incorrect' => [
                    'From 1914 to 1918',
                    'The signing occurred on November 11, 1918',
                    'It had minimal impact on global politics',
                    'It primarily affected agricultural practices',
                    'It resulted in decreased international trade',
                    'It was a period of scientific regression',
                    'It strengthened colonial powers',
                    'It slowed technological development',
                    'It had little influence on modern governance',
                    'It maintained traditional economic structures'
                ]
            ],
        ];
        
        $optionType = $isCorrect ? 'correct' : 'incorrect';
        $categoryOptions = $options[$category][$optionType];
        
        return $categoryOptions[array_rand($categoryOptions)];
    }
}