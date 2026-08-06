import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const subjectIndexScript = readFileSync(
    new URL(
        '../../resources/views/subjects/partials/index-script.blade.php',
        import.meta.url,
    ),
    'utf8',
);

function extractFunction(source, name) {
    const start = source.indexOf(`function ${name}(`);
    assert.notEqual(start, -1, `${name} must exist`);
    const bodyStart = source.indexOf('{', start);
    let depth = 0;

    for (let index = bodyStart; index < source.length; index += 1) {
        if (source[index] === '{') depth += 1;
        if (source[index] === '}') depth -= 1;
        if (depth === 0) return source.slice(start, index + 1);
    }

    throw new Error(`Unable to extract ${name}`);
}

function createModalFixture() {
    const selectors = [
        'code-name',
        'secondary-name',
        'status',
        'credits',
        'lecture-credits',
        'lab-credits',
        'self-study-credits',
        'lecture-hours',
        'lab-hours',
        'self-study-hours',
        'source',
    ];
    const targets = Object.fromEntries(
        selectors.map((name) => [name, {
            textContent: '',
            classList: {
                add() {},
                remove() {},
            },
        }]),
    );

    return {
        modal: {
            querySelector(selector) {
                const match = selector.match(/^\[data-subject-detail-(.+)]$/);
                return match ? targets[match[1]] ?? null : null;
            },
        },
        targets,
    };
}

function runPopulate(trigger) {
    const { modal, targets } = createModalFixture();
    const context = vm.createContext({});
    vm.runInContext(extractFunction(subjectIndexScript, 'populateSubjectDetailModal'), context);
    context.populateSubjectDetailModal(trigger, modal);

    return targets;
}

test('subject detail modal displays hours as the table tuple source', () => {
    const targets = runPopulate({
        dataset: {
            code: '1499202-3',
            displayName: 'Environmental Health',
            secondaryName: 'อนามัยสิ่งแวดล้อม',
            credits: '3',
            lectureCredits: '3',
            labCredits: '0',
            selfStudyCredits: '6',
            lectureHours: '0',
            labHours: '2',
            selfStudyHours: '0',
            isActive: '1',
            displaySource: 'hours',
            displayLecture: '0',
            displayLab: '2',
            displaySelfStudy: '0',
        },
    });

    assert.equal(targets['code-name'].textContent, '1499202-3: Environmental Health');
    assert.equal(targets.credits.textContent, '3');
    assert.equal(targets['lab-hours'].textContent, '2');
    assert.equal(targets.source.textContent, 'ค่าที่แสดงในตาราง: ชั่วโมง ( 0 / 2 / 0 )');
    assert.equal(targets.status.textContent, 'เปิดใช้งาน');
});

test('subject detail modal displays credits when every hour is zero', () => {
    const targets = runPopulate({
        dataset: {
            code: 'ZERO101',
            displayName: 'Zero Hours',
            credits: '3',
            lectureCredits: '3',
            labCredits: '0',
            selfStudyCredits: '6',
            lectureHours: '0',
            labHours: '0',
            selfStudyHours: '0',
            isActive: '0',
            displaySource: 'credits',
            displayLecture: '3',
            displayLab: '0',
            displaySelfStudy: '6',
        },
    });

    assert.equal(targets.source.textContent, 'ค่าที่แสดงในตาราง: หน่วยกิต ( 3 / 0 / 6 )');
    assert.equal(targets.status.textContent, 'ปิดใช้งาน');
});

test('opening subject details populates and shows the shared modal', () => {
    const trigger = { dataset: {} };
    const modal = {};
    let populatedWith = null;
    let shown = false;
    const context = vm.createContext({
        document: {
            getElementById(id) {
                return id === 'subjectDetailModal' ? modal : null;
            },
        },
        populateSubjectDetailModal(subjectTrigger, subjectModal) {
            populatedWith = [subjectTrigger, subjectModal];
        },
        window: {
            bootstrap: {
                Modal: {
                    getOrCreateInstance(subjectModal) {
                        assert.equal(subjectModal, modal);
                        return { show() { shown = true; } };
                    },
                },
            },
        },
    });
    vm.runInContext(extractFunction(subjectIndexScript, 'openSubjectDetailModal'), context);

    context.openSubjectDetailModal(trigger);

    assert.deepEqual(populatedWith, [trigger, modal]);
    assert.equal(shown, true);
});
