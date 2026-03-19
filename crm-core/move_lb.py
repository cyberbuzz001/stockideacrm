with open('c:/stockideacrm/crm-core/resources/views/dashboard.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

start_idx = -1
end_idx = -1

for i, line in enumerate(lines):
    if 'LIVE LEADERBOARD (visible to ALL roles)' in line:
        start_idx = i - 1
    if 'MAIN GRID: ROLE-SPECIFIC CONTENT' in line:
        end_idx = i - 2
        break

if start_idx != -1 and end_idx != -1:
    leaderboard_lines = lines[start_idx:end_idx+1]
    del lines[start_idx:end_idx+1]

    insert_idx = -1
    for i, line in enumerate(lines):
        if "@if($role === 'Admin')" in line:
            insert_idx = i
            break
            
    if insert_idx != -1:
        lines = lines[:insert_idx] + leaderboard_lines + ['\t    <br>\n'] + lines[insert_idx:]
        
    with open('c:/stockideacrm/crm-core/resources/views/dashboard.blade.php', 'w', encoding='utf-8') as f:
        f.writelines(lines)
        print('Success moving leaderboards!')
else:
    print(f'Failed to find boundaries: start={start_idx}, end={end_idx}')
