<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordEntry;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicalRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $helena = User::where('email', 'helena@teste')->first();
        $bruno  = User::where('email', 'bruno@teste')->first();

        $ana     = Patient::whereHas('user', fn ($q) => $q->where('email', 'ana@teste'))->first();
        $marcos  = Patient::whereHas('user', fn ($q) => $q->where('email', 'marcos@teste'))->first();
        $beatriz = Patient::whereHas('user', fn ($q) => $q->where('email', 'beatriz@teste'))->first();

        // ── Ana Carolina — TAG ───────────────────────────────────────────
        $recordAna = MedicalRecord::firstOrCreate(['patient_id' => $ana->id]);

        $apptAna1 = Appointment::where('patient_id', $ana->id)
            ->where('status', 'DONE')
            ->orderBy('starts_at')
            ->first();

        $apptAna2 = Appointment::where('patient_id', $ana->id)
            ->where('status', 'DONE')
            ->orderBy('starts_at')
            ->skip(1)->first();

        if ($apptAna1 && ! MedicalRecordEntry::where('appointment_id', $apptAna1->id)->exists()) {
            MedicalRecordEntry::create([
                'medical_record_id'  => $recordAna->id,
                'author_id'          => $helena->id,
                'appointment_id'     => $apptAna1->id,
                'entry_type'         => 'ANAMNESIS',
                'subjective'         => 'Paciente relata ansiedade persistente há cerca de 8 meses, com dificuldade para relaxar, preocupações excessivas com trabalho e família, insônia inicial e tensão muscular constante. Nega uso de substâncias. Nega ideação suicida.',
                'objective'          => 'Paciente orientada no tempo e espaço, colaborativa, discurso coerente e fluente. Sem alterações formais do pensamento. Humor ansioso. Afeto congruente ao conteúdo.',
                'assessment'         => 'Transtorno de Ansiedade Generalizada — CID F41.1.',
                'plan'               => 'Iniciado escitalopram 10mg/dia pela manhã. Psicoeducação sobre o TAG e sobre mecanismos de resposta ao estresse. Orientada sobre higiene do sono. Retorno em 4 semanas.',
                'confidential_notes' => null,
                'created_at'         => $apptAna1->starts_at,
                'updated_at'         => $apptAna1->starts_at,
            ]);
        }

        if ($apptAna2 && ! MedicalRecordEntry::where('appointment_id', $apptAna2->id)->exists()) {
            MedicalRecordEntry::create([
                'medical_record_id'  => $recordAna->id,
                'author_id'          => $helena->id,
                'appointment_id'     => $apptAna2->id,
                'entry_type'         => 'FOLLOW_UP',
                'subjective'         => 'Paciente refere melhora parcial da ansiedade após 4 semanas de uso do escitalopram. Insônia inicial persiste, embora com menor intensidade. Nega efeitos adversos significativos. Mantém preocupações com desempenho profissional.',
                'objective'          => 'Humor levemente ansioso, visivelmente melhor que a consulta anterior. Discurso fluente. Sem alterações psicóticas. Sono referido de 5 a 6h por noite.',
                'assessment'         => 'TAG em melhora parcial com ISRS após 4 semanas.',
                'plan'               => 'Manter escitalopram 10mg/dia. Reforçar técnicas de higiene do sono. Sugerida avaliação para psicoterapia cognitivo-comportamental. Retorno em 6 semanas.',
                'confidential_notes' => null,
                'created_at'         => $apptAna2->starts_at,
                'updated_at'         => $apptAna2->starts_at,
            ]);
        }

        // ── Marcos Oliveira — Depressão ──────────────────────────────────
        $recordMarcos = MedicalRecord::firstOrCreate(['patient_id' => $marcos->id]);

        $apptMarcos1 = Appointment::where('patient_id', $marcos->id)
            ->where('status', 'DONE')
            ->orderBy('starts_at')
            ->first();

        if ($apptMarcos1 && ! MedicalRecordEntry::where('appointment_id', $apptMarcos1->id)->exists()) {
            MedicalRecordEntry::create([
                'medical_record_id'  => $recordMarcos->id,
                'author_id'          => $helena->id,
                'appointment_id'     => $apptMarcos1->id,
                'entry_type'         => 'CONSULTATION',
                'subjective'         => 'Paciente relata humor deprimido há aproximadamente 3 meses, com piora após demissão do emprego. Refere anedonia, hipersonia (dormindo 12h/dia), fadiga intensa e dificuldade de concentração. Relata pensamentos passivos de morte ("seria melhor não acordar"), porém sem plano ou intenção.',
                'objective'          => 'Paciente com fácies triste, postura curvada. Discurso lentificado, voz baixa. Orientado no tempo e espaço. Sem alterações formais do pensamento. Sem sinais de psicose.',
                'assessment'         => 'Episódio Depressivo Moderado — CID F32.1.',
                'plan'               => 'Iniciado sertralina 50mg/dia com refeição. Encaminhamento para psicoterapia (TCC). Retorno em 3 semanas. Paciente e familiar orientados sobre sinais de alerta e como acionar suporte.',
                'confidential_notes' => 'Pensamentos passivos de morte presentes. Sem plano elaborado. Paciente relativamente estável mas requer monitoramento próximo. Familiar (esposa) ciente da gravidade e orientada. Reavaliar ideação no próximo retorno.',
                'created_at'         => $apptMarcos1->starts_at,
                'updated_at'         => $apptMarcos1->starts_at,
            ]);
        }

        // ── Beatriz Lima — Transtorno Bipolar ───────────────────────────
        $recordBeatriz = MedicalRecord::firstOrCreate(['patient_id' => $beatriz->id]);

        $apptsBeatriz = Appointment::where('patient_id', $beatriz->id)
            ->where('status', 'DONE')
            ->orderBy('starts_at')
            ->get();

        $entryTypes = ['CONSULTATION', 'FOLLOW_UP'];
        $entries = [
            [
                'entry_type'         => 'CONSULTATION',
                'subjective'         => 'Paciente de 25 anos trazida pela mãe após episódio de agitação, redução do sono para 2h por noite sem cansaço, gastos impulsivos e fala acelerada há 10 dias. História prévia de dois episódios depressivos, tratados com antidepressivo isolado. Nega uso de substâncias.',
                'objective'          => 'Paciente agitada, com discurso acelerado e tangencial. Humor elevado/irritável. Sem alucinações. Juízo de realidade prejudicado.',
                'assessment'         => 'Episódio maníaco sem sintomas psicóticos, provável Transtorno Afetivo Bipolar Tipo I — CID F30.1. Avaliar se episódios depressivos anteriores foram induzidos por antidepressivo sem estabilizador.',
                'plan'               => 'Suspenso antidepressivo. Iniciado valproato de sódio 500mg/dia, com titulação gradual. Orientações à família sobre segurança. Retorno em 2 semanas.',
                'confidential_notes' => 'Hipótese de TAB Tipo I. Não informei o diagnóstico completo à paciente neste momento dada a agitação — prefiro abordar com ela mais estável. Familiar informado.',
            ],
            [
                'entry_type'         => 'FOLLOW_UP',
                'subjective'         => 'Paciente refere melhora significativa após 4 semanas. Sono normalizado (7-8h). Fala que se sente "mais em si". Tolera bem o valproato, com leve ganho de peso relatado.',
                'objective'          => 'Paciente calma, colaborativa, discurso fluente e coerente. Humor eutímico. Sem sintomas maníacos ativos.',
                'assessment'         => 'Episódio maníaco em remissão com estabilizador de humor.',
                'plan'               => 'Manter valproato 500mg/dia. Solicitar valproemia após 1 mês. Psicoeducação sobre TAB. Discutido contrato de crise. Retorno em 4 semanas.',
                'confidential_notes' => 'Informei diagnóstico de TAB à paciente de forma gradual — recebeu bem, demonstrou alívio por ter uma explicação. Família presente e engajada.',
            ],
        ];

        foreach ($apptsBeatriz as $i => $appt) {
            if (isset($entries[$i]) && ! MedicalRecordEntry::where('appointment_id', $appt->id)->exists()) {
                MedicalRecordEntry::create(array_merge($entries[$i], [
                    'medical_record_id' => $recordBeatriz->id,
                    'author_id'         => $bruno->id,
                    'appointment_id'    => $appt->id,
                    'created_at'        => $appt->starts_at,
                    'updated_at'        => $appt->starts_at,
                ]));
            }
        }
    }
}